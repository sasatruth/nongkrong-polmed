document.addEventListener('DOMContentLoaded', function () {
    var spatialData  = window.spatialData || {};
    var kampusCenter = spatialData.kampus_center || [3.5626, 98.6569];

    /* ═══════════════════════════════════════════════════
       MAP INIT
    ═══════════════════════════════════════════════════ */
    var map = L.map('map', { zoomControl: true }).setView(kampusCenter, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    /* ═══════════════════════════════════════════════════
       CONSTANTS
    ═══════════════════════════════════════════════════ */
    var PIN_COLOR = {
        kafe:   '#FF6B35',
        warkop: '#4ECDC4',
        resto:  '#45B7D1',
        taman:  '#96CEB4',
    };

    var FAC_INFO = {
        wifi:         { icon: '📶', label: 'Free Wifi' },
        ac:           { icon: '❄️',  label: 'AC' },
        colokan:      { icon: '🔌', label: 'Colokan' },
        outdoor:      { icon: '🪑', label: 'Outdoor' },
        musholla:     { icon: '🕌', label: 'Musholla' },
        parkir_mobil: { icon: '🚗', label: 'Parkir Mobil' },
        toilet:       { icon: '🚻', label: 'Toilet' },
    };

    /* ═══════════════════════════════════════════════════
       LAYER GROUPS
    ═══════════════════════════════════════════════════ */
    var pointLayer  = L.layerGroup().addTo(map);
    var routeLayer  = L.layerGroup().addTo(map);
    var kampusLayer = L.layerGroup().addTo(map);

    /* ═══════════════════════════════════════════════════
       STATE
    ═══════════════════════════════════════════════════ */
    var markers          = [];
    var activeFacFilters = [];
    var currentMode      = 'point';

    /* ═══════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════ */
    function normalizeKat(v) {
        if (!v) return 'kafe';
        var s = String(v).toLowerCase().trim().replace(/\s+/g, '');
        if (s.includes('warkop')) return 'warkop';
        if (s.includes('resto'))  return 'resto';
        if (s.includes('taman'))  return 'taman';
        return 'kafe';
    }

    function katEmoji(k) {
        return { kafe: '☕', warkop: '🍵', resto: '🍽️', taman: '🌳' }[k] || '📍';
    }

    function escHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmtRp(n) {
        return Number(n || 0).toLocaleString('id-ID');
    }

    /* ═══════════════════════════════════════════════════
       DECODE OSRM POLYLINE (precision 5)
    ═══════════════════════════════════════════════════ */
    function decodePolyline(encoded) {
        var points = [], index = 0, lat = 0, lng = 0;
        while (index < encoded.length) {
            var b, shift = 0, result = 0;
            do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
            lat += (result & 1) ? ~(result >> 1) : (result >> 1);
            shift = 0; result = 0;
            do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
            lng += (result & 1) ? ~(result >> 1) : (result >> 1);
            points.push([lat / 1e5, lng / 1e5]);
        }
        return points;
    }

    /* ═══════════════════════════════════════════════════
       ROUTING — OSRM API
    ═══════════════════════════════════════════════════ */
    var routeQueue   = [];
    var routeRunning = 0;
    var ROUTE_LIMIT  = 3;

    function enqueueRoute(job) { routeQueue.push(job); flushQueue(); }

    function flushQueue() {
        while (routeRunning < ROUTE_LIMIT && routeQueue.length > 0) {
            var job = routeQueue.shift();
            routeRunning++;
            (function (j) {
                fetchOSRMRoute(j.from, j.to, j.color, j.nama, function (group) {
                    routeRunning--;
                    j.onDone(group);
                    flushQueue();
                });
            })(job);
        }
    }

    function fetchOSRMRoute(from, to, color, nama, onDone) {
        var url = 'https://router.project-osrm.org/route/v1/driving/'
            + from[1] + ',' + from[0] + ';'
            + to[1]   + ',' + to[0]
            + '?overview=full&geometries=polyline&steps=true';

        fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                    var r      = data.routes[0];
                    var coords = decodePolyline(r.geometry);
                    var distKm = (r.distance / 1000).toFixed(1);
                    var durMin = Math.ceil(r.duration / 60);
                    // Parse turn-by-turn steps
                    var steps = [];
                    if (r.legs && r.legs[0] && r.legs[0].steps) {
                        steps = r.legs[0].steps.map(function(s) {
                            return {
                                maneuver: s.maneuver ? s.maneuver.type : '',
                                modifier: s.maneuver ? (s.maneuver.modifier || '') : '',
                                name: s.name || '',
                                distance: s.distance ? Math.round(s.distance) : 0,
                            };
                        });
                    }
                    onDone(buildRouteGroup(coords, color, nama, distKm, durMin, steps));
                } else {
                    onDone(buildRouteGroup([from, to], color, nama, '?', '?', []));
                }
            })
            .catch(function () {
                onDone(buildRouteGroup([from, to], color, nama, '?', '?', []));
            });
    }

    /* ═══════════════════════════════════════════════════
       BUILD ROUTE GROUP — Google Maps style
    ═══════════════════════════════════════════════════ */
    function buildRouteGroup(coords, color, nama, distKm, durMin, steps) {
        var layers = [];

        // 1. Glow / shadow luar
        layers.push(L.polyline(coords, {
            color: color, weight: 16, opacity: 0.12,
            lineCap: 'round', lineJoin: 'round', interactive: false,
        }));

        // 2. Border putih
        layers.push(L.polyline(coords, {
            color: '#ffffff', weight: 11, opacity: 0.85,
            lineCap: 'round', lineJoin: 'round', interactive: false,
        }));

        // 3. Garis warna utama
        var mainLine = L.polyline(coords, {
            color: color, weight: 6, opacity: 1,
            lineCap: 'round', lineJoin: 'round',
        });
        layers.push(mainLine);

        // 4. Panah arah di setiap interval
        var step = Math.max(4, Math.floor(coords.length / 10));
        for (var i = step; i < coords.length - 1; i += step) {
            var p1 = coords[i];
            var p2 = coords[Math.min(i + 2, coords.length - 1)];
            var dx  = p2[1] - p1[1];
            var dy  = p2[0] - p1[0];
            var deg = Math.atan2(dx, dy) * 180 / Math.PI;

            layers.push(L.marker([p1[0], p1[1]], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;">'
                        + '<div style="width:0;height:0;'
                        + 'border-left:6px solid transparent;'
                        + 'border-right:6px solid transparent;'
                        + 'border-bottom:11px solid ' + color + ';'
                        + 'filter:drop-shadow(0 0 2px rgba(255,255,255,0.9));'
                        + 'transform:rotate(' + deg + 'deg);'
                        + '"></div></div>',
                    iconSize:   [20, 20],
                    iconAnchor: [10, 10],
                }),
                interactive: false,
                zIndexOffset: -200,
            }));
        }

        // 5. Titik akhir destinasi
        var last = coords[coords.length - 1];
        layers.push(L.circleMarker([last[0], last[1]], {
            radius: 8, color: '#fff', fillColor: color,
            fillOpacity: 1, weight: 3, interactive: false,
        }));

        // 6. Tooltip — jarak, waktu, dan petunjuk arah ringkas
        var stepsHtml = '';
        if (steps && steps.length > 0) {
            var maneuverIcon = {
                'turn': { 'left': '↰', 'right': '↱', 'slight left': '↖', 'slight right': '↗', 'sharp left': '↰', 'sharp right': '↱' },
                'depart': '▶',
                'arrive': '🏁',
                'merge': '⇒',
                'on ramp': '↗',
                'off ramp': '↘',
                'fork': '⑂',
                'end of road': '↲',
                'continue': '↑',
                'roundabout': '↻',
                'rotary': '↻',
                'roundabout turn': '↻',
                'notification': 'ℹ',
            };

            var displaySteps = steps.filter(function(s) {
                return s.maneuver !== 'depart' || steps.indexOf(s) === 0;
            }).slice(0, 6); // maks 6 langkah

            stepsHtml = '<div style="margin-top:8px;border-top:1px solid rgba(255,255,255,0.2);padding-top:8px;">'
                + '<div style="font-size:.72rem;font-weight:700;letter-spacing:.5px;margin-bottom:5px;opacity:.85;">PETUNJUK ARAH</div>';
            displaySteps.forEach(function(s) {
                var icon = '↑';
                if (s.maneuver === 'arrive') icon = '🏁';
                else if (s.maneuver === 'depart') icon = '▶';
                else if (s.maneuver === 'turn') {
                    var t = maneuverIcon['turn'];
                    icon = t[s.modifier] || '↑';
                } else if (maneuverIcon[s.maneuver]) {
                    icon = maneuverIcon[s.maneuver];
                }
                var dist = s.distance >= 1000
                    ? (s.distance/1000).toFixed(1) + ' km'
                    : s.distance + ' m';
                stepsHtml += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;font-size:.78rem;">'
                    + '<span style="font-size:1rem;flex-shrink:0;">' + icon + '</span>'
                    + '<span><b>' + escHtml(s.name || 'Lanjut') + '</b>'
                    + (s.distance ? ' <span style="opacity:.7;">' + dist + '</span>' : '') + '</span>'
                    + '</div>';
            });
            if (steps.length > 6) {
                stepsHtml += '<div style="font-size:.72rem;opacity:.65;font-style:italic;">... dan ' + (steps.length - 6) + ' langkah lagi</div>';
            }
            stepsHtml += '</div>';
        }

        mainLine.bindTooltip(
            '<div style="font-family:\'Plus Jakarta Sans\',sans-serif;padding:10px 14px;line-height:1.6;min-width:200px;">'
            + '<b style="display:block;font-size:.95rem;color:#fff;margin-bottom:3px;">' + escHtml(nama) + '</b>'
            + '<div style="display:flex;gap:12px;font-size:.82rem;margin-bottom:2px;">'
            +   '<span>📏 <b>' + distKm + ' km</b></span>'
            +   '<span>🕐 <b>±' + durMin + ' menit</b></span>'
            + '</div>'
            + stepsHtml
            + '</div>',
            { sticky: true, direction: 'top', offset: [0, -10], className: 'route-tooltip-box' }
        );

        return L.layerGroup(layers);
    }

    /* ═══════════════════════════════════════════════════
       POPUP HTML — Detail Fasilitas Lengkap di Peta
    ═══════════════════════════════════════════════════ */
    function buildPopupHTML(d) {
        var kat   = d.kategori || 'kafe';
        var color = PIN_COLOR[kat] || '#B08968';
        var fasList = (d.fasilitas || '').split(',').filter(Boolean);

        // Bintang rating
        var rNum  = parseFloat(d.rating) || 0;
        var stars = '';
        for (var i = 1; i <= 5; i++) {
            stars += '<span style="font-size:15px;color:' + (i <= Math.round(rNum) ? '#F59E0B' : '#D1D5DB') + ';">★</span>';
        }

        // ── Fasilitas grid (semua item ditampilkan, yang tidak tersedia di-grey) ──
        var fasHTML = '<div class="pp-fac-wrap">'
            + '<div class="pp-fac-title">🛎️ Fasilitas</div>'
            + '<div class="pp-fac-grid">';

        Object.keys(FAC_INFO).forEach(function(f) {
            var info    = FAC_INFO[f];
            var tersedia = fasList.includes(f);
            fasHTML += '<div class="pp-fac-item' + (tersedia ? '' : ' pp-fac-item--off') + '">'
                + '<span class="pp-fac-icon">' + info.icon + '</span>'
                + '<span class="pp-fac-lbl">' + info.label + '</span>'
                + (tersedia
                    ? '<span class="pp-fac-status pp-fac-status--on">✓</span>'
                    : '<span class="pp-fac-status pp-fac-status--off">✗</span>')
                + '</div>';
        });

        fasHTML += '</div></div>';

        return '<div class="pp-card">'

            // ── Header berwarna ──
            + '<div class="pp-header" style="background:linear-gradient(135deg,' + color + 'EE,' + color + 'BB);">'
            +   '<div class="pp-hd-emoji">' + katEmoji(kat) + '</div>'
            +   '<div class="pp-hd-info">'
            +     '<div class="pp-hd-name">' + escHtml(d.nama || '-') + '</div>'
            +     '<span class="pp-hd-kat">' + kat.toUpperCase() + '</span>'
            +   '</div>'
            + '</div>'

            // ── Body ──
            + '<div class="pp-body">'

            // Rating & Harga
            + '<div class="pp-meta">'
            +   '<div class="pp-meta-item">'
            +     '<div class="pp-meta-lbl">Rating</div>'
            +     '<div>' + stars + ' <b style="font-size:.85rem;color:#3B2A22;">' + (rNum || '-') + '</b></div>'
            +   '</div>'
            +   '<div class="pp-meta-sep"></div>'
            +   '<div class="pp-meta-item">'
            +     '<div class="pp-meta-lbl">Harga</div>'
            +     '<div class="pp-price">Rp ' + fmtRp(d.hargaMin) + ' – ' + fmtRp(d.hargaMax) + '</div>'
            +   '</div>'
            + '</div>'

            // Info baris
            + (d.alamat  ? '<div class="pp-info-row"><span>📍</span><span>' + escHtml(d.alamat) + '</span></div>' : '')
            + (d.jam     ? '<div class="pp-info-row"><span>🕐</span><b>' + escHtml(d.jam) + '</b></div>' : '')
            + (d.kontak  ? '<div class="pp-info-row"><span>📲</span><span>' + escHtml(d.kontak) + '</span></div>' : '')
            + (d.catatan ? '<div class="pp-catatan">📝 ' + escHtml(d.catatan) + '</div>' : '')

            // ── Fasilitas Lengkap ──
            + fasHTML

            // ── Tombol Rute ──
            + '<button class="pp-route-btn" onclick="showOnlyRoute(' + d.lat + ',' + d.lng + ')">'
            + '🗺️ Tampilkan Rute Jalan dari Kampus'
            + '</button>'

            + '</div>' // pp-body
            + '</div>'; // pp-card
    }

    /* ═══════════════════════════════════════════════════
       KAMPUS MARKER
    ═══════════════════════════════════════════════════ */
    function createKampusMarker() {
        var icon = L.divIcon({
            className: '',
            html: '<div class="kampus-pin"><div class="kampus-pulse"></div><div class="kampus-core">🏫</div></div>',
            iconSize: [56, 56], iconAnchor: [28, 28], popupAnchor: [0, -32],
        });
        L.marker(kampusCenter, { icon: icon, zIndexOffset: 9999 })
            .addTo(kampusLayer)
            .bindPopup(
                '<div class="pp-card">'
                + '<div class="pp-header" style="background:linear-gradient(135deg,#2D6A4F,#1B4332);">'
                + '<div class="pp-hd-emoji">🏫</div>'
                + '<div class="pp-hd-info"><div class="pp-hd-name">Politeknik Negeri Medan</div>'
                + '<span class="pp-hd-kat">TITIK AWAL RUTE</span></div></div>'
                + '<div class="pp-body">'
                + '<div class="pp-info-row"><span>📍</span><span>Jl. Almamater No.1, Medan</span></div>'
                + '<div class="pp-info-row"><span>ℹ️</span><span>Semua rute dihitung dari titik ini</span></div>'
                + '<div class="pp-info-row"><span>🗺️</span><span>Klik marker tempat → tampil rute jalan nyata</span></div>'
                + '</div></div>',
                { maxWidth: 320, className: 'pp-popup-wrapper' }
            );
    }

    /* ═══════════════════════════════════════════════════
       PLACE MARKER ICON
    ═══════════════════════════════════════════════════ */
    function makePlaceIcon(kat) {
        var color = PIN_COLOR[kat] || '#B08968';
        return L.divIcon({
            className: '',
            html: '<div class="place-pin" style="--pc:' + color + '">'
                + '<div class="place-pin-body">' + katEmoji(kat) + '</div>'
                + '</div>',
            iconSize: [44, 52], iconAnchor: [22, 52], popupAnchor: [0, -56],
        });
    }

    /* ═══════════════════════════════════════════════════
       CONVEX HULL (untuk mode polygon)
    ═══════════════════════════════════════════════════ */
    function convexHull(points) {
        if (points.length < 3) return points;
        var cross = function(O, A, B) {
            return (A[0]-O[0])*(B[1]-O[1]) - (A[1]-O[1])*(B[0]-O[0]);
        };
        var sorted = points.slice().sort(function(a,b){ return a[0]-b[0] || a[1]-b[1]; });
        var lower = [];
        sorted.forEach(function(p) {
            while (lower.length >= 2 && cross(lower[lower.length-2], lower[lower.length-1], p) <= 0) lower.pop();
            lower.push(p);
        });
        var upper = [];
        sorted.slice().reverse().forEach(function(p) {
            while (upper.length >= 2 && cross(upper[upper.length-2], upper[upper.length-1], p) <= 0) upper.pop();
            upper.push(p);
        });
        upper.pop(); lower.pop();
        return lower.concat(upper);
    }

    /* ═══════════════════════════════════════════════════
       LAYER MODE (Point / Line / Polygon / All)
    ═══════════════════════════════════════════════════ */
    window.setLayerMode = function(mode) {
        currentMode = mode;
        ['point','line','polygon','all'].forEach(function(m) {
            var btn = document.getElementById('btn-' + m);
            if (btn) btn.classList.toggle('active', m === mode);
        });
        updateFilters();
    };

    /* ═══════════════════════════════════════════════════
       GLOBALS — dipanggil dari HTML/popup
    ═══════════════════════════════════════════════════ */
    window.showOnlyRoute = function (lat, lng) {
        // Sembunyikan semua rute dulu
        markers.forEach(function (m) {
            if (m.routeGroup) routeLayer.removeLayer(m.routeGroup);
        });
        // Tampilkan hanya rute yang diminta
        var found = markers.find(function (m) {
            return Math.abs(m.lat - lat) < 0.00001 && Math.abs(m.lng - lng) < 0.00001;
        });
        if (found && found.routeGroup) {
            routeLayer.addLayer(found.routeGroup);
            map.fitBounds(
                L.latLngBounds([kampusCenter, [lat, lng]]),
                { padding: [80, 80], animate: true }
            );
        }
        // Pastikan routeLayer tampil
        if (!map.hasLayer(routeLayer)) map.addLayer(routeLayer);
    };

    window.focusMarker = function (lat, lng) {
        lat = parseFloat(lat); lng = parseFloat(lng);
        if (!lat || !lng) return;
        var found = markers.find(function (m) {
            return Math.abs(m.lat - lat) < 0.00001 && Math.abs(m.lng - lng) < 0.00001;
        });
        if (found) {
            // Tampilkan rute khusus marker ini
            markers.forEach(function (m) { if (m.routeGroup) routeLayer.removeLayer(m.routeGroup); });
            if (found.routeGroup) routeLayer.addLayer(found.routeGroup);
            if (!map.hasLayer(found.marker)) pointLayer.addLayer(found.marker);
            found.marker.openPopup();
            map.fitBounds(
                L.latLngBounds([kampusCenter, [lat, lng]]),
                { padding: [80, 80], animate: true }
            );
        }
        document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    /* ═══════════════════════════════════════════════════
       LOAD MARKERS dari place-card
    ═══════════════════════════════════════════════════ */
    document.querySelectorAll('.place-card').forEach(function (card) {
        var lat = parseFloat(card.dataset.lat) || 0;
        var lng = parseFloat(card.dataset.lng) || 0;
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) return;
        // Validasi koordinat Indonesia
        if (lat < -11 || lat > 6 || lng < 95 || lng > 141) return;

        var hargaMax  = parseFloat(card.dataset.harga)    || 0;
        var hargaMin  = parseFloat(card.dataset.hargaMin) || 0;
        var rating    = parseFloat(card.dataset.rating)   || 0;
        var kategori  = normalizeKat(card.dataset.kategori);
        var fasilitas = card.dataset.fasilitas || '';
        var nama      = card.dataset.nama    || '';
        var alamat    = card.dataset.alamat  || '';
        var jam       = card.dataset.jam     || '';
        var kontak    = card.dataset.kontak  || '';
        var catatan   = card.dataset.catatan || '';

        var marker = L.marker([lat, lng], {
            icon: makePlaceIcon(kategori),
            riseOnHover: true, zIndexOffset: 1000,
        })
        .addTo(pointLayer)
        .bindPopup(buildPopupHTML({
            nama: nama, kategori: kategori, rating: rating,
            hargaMin: hargaMin, hargaMax: hargaMax,
            alamat: alamat, jam: jam, kontak: kontak,
            catatan: catatan, fasilitas: fasilitas,
            lat: lat, lng: lng,
        }), { maxWidth: 380, keepInView: true, closeButton: true, autoClose: false, className: 'pp-popup-wrapper' });

        var markerObj = {
            marker: marker, card: card,
            lat: lat, lng: lng,
            hargaMax: hargaMax, hargaMin: hargaMin,
            rating: rating, kategori: kategori,
            fasilitas: fasilitas, nama: nama, alamat: alamat,
            routeGroup: null,
        };
        markers.push(markerObj);

        // Klik marker → tampilkan rute ke marker ini
        marker.on('click', (function (obj) {
            return function () {
                markers.forEach(function (m) { if (m.routeGroup) routeLayer.removeLayer(m.routeGroup); });
                if (obj.routeGroup) {
                    routeLayer.addLayer(obj.routeGroup);
                    if (!map.hasLayer(routeLayer)) map.addLayer(routeLayer);
                }
                map.setView([obj.lat, obj.lng], 16, { animate: true });
            };
        })(markerObj));

        // Fetch rute OSRM
        var color = PIN_COLOR[kategori] || '#B08968';
        enqueueRoute({
            from: kampusCenter, to: [lat, lng], color: color, nama: nama,
            onDone: function (group) {
                markerObj.routeGroup = group;
                // Default: tampilkan semua rute di mode 'all', sembunyi di mode 'point'
                if (currentMode === 'line' || currentMode === 'all') {
                    routeLayer.addLayer(group);
                }
            },
        });
    });

    /* ═══════════════════════════════════════════════════
       FILTER & UPDATE LAYERS
    ═══════════════════════════════════════════════════ */
    var searchInput = document.getElementById('searchInput');
    var hargaSlider = document.getElementById('harga');
    var hargaLabel  = document.getElementById('hargaLabel');
    var starsEl     = document.querySelectorAll('.star');
    var minRatingEl = document.getElementById('minRating');

    function syncHargaLabel() {
        if (hargaSlider && hargaLabel)
            hargaLabel.textContent = 'Rp' + Number(hargaSlider.value).toLocaleString('id-ID');
    }

    function updateFilters() {
        var maxHarga   = hargaSlider ? parseFloat(hargaSlider.value) : Infinity;
        var minRating  = minRatingEl ? parseFloat(minRatingEl.value) || 0 : 0;
        var query      = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var katChecked = Array.from(document.querySelectorAll('.kategori:checked'))
                             .map(function (cb) { return normalizeKat(cb.value); });
        var fasChecked = Array.from(document.querySelectorAll('.fasilitas:checked'))
                             .map(function (cb) { return cb.value; });

        // Hapus semua layer dulu
        pointLayer.clearLayers();
        routeLayer.clearLayers();

        var visibleCoordsByKat = {};
        var visibleBounds = [kampusCenter];

        markers.forEach(function (item) {
            var fasList = item.fasilitas ? item.fasilitas.split(',').filter(Boolean) : [];
            var ok = item.hargaMax <= maxHarga
                && item.rating >= minRating
                && (katChecked.length === 0 || katChecked.includes(item.kategori))
                && (fasChecked.length === 0 || fasChecked.every(function (f) { return fasList.includes(f); }))
                && (activeFacFilters.length === 0 || activeFacFilters.every(function (f) { return fasList.includes(f); }))
                && (!query || [item.nama, item.alamat, item.kategori, item.fasilitas].join(' ').toLowerCase().includes(query));

            item.card.style.display = ok ? '' : 'none';

            if (!ok) return;

            // Point selalu ditampilkan jika lolos filter
            pointLayer.addLayer(item.marker);
            visibleBounds.push([item.lat, item.lng]);

            // Kumpulkan koordinat per kategori (untuk polygon)
            if (!visibleCoordsByKat[item.kategori]) visibleCoordsByKat[item.kategori] = [];
            visibleCoordsByKat[item.kategori].push([item.lat, item.lng]);

            // Line / route
            if ((currentMode === 'line' || currentMode === 'all') && item.routeGroup) {
                routeLayer.addLayer(item.routeGroup);
            }
        });

        // Polygon mode
        if (currentMode === 'polygon' || currentMode === 'all') {
            Object.keys(visibleCoordsByKat).forEach(function(kat) {
                var coords = visibleCoordsByKat[kat];
                var col    = PIN_COLOR[kat] || '#B08968';
                var shape;
                if (coords.length >= 3) {
                    var hull = convexHull(coords);
                    shape = L.polygon(hull.length >= 3 ? hull : coords, {
                        color: col, weight: 2.5, fillColor: col, fillOpacity: 0.13, opacity: 0.8,
                        dashArray: '6,4'
                    }).bindTooltip('Area ' + kat.charAt(0).toUpperCase() + kat.slice(1), { sticky: true });
                } else if (coords.length === 2) {
                    var off = 0.0008;
                    var a = coords[0], b = coords[1];
                    shape = L.polygon([
                        [a[0]-off,a[1]-off],[a[0]+off,a[1]+off],
                        [b[0]+off,b[1]+off],[b[0]-off,b[1]-off]
                    ], { color: col, weight: 2, fillColor: col, fillOpacity: 0.12, opacity: 0.8 });
                } else {
                    shape = L.circle(coords[0], {
                        radius: 200, color: col, weight: 2, fillColor: col, fillOpacity: 0.13
                    });
                }
                routeLayer.addLayer(shape);
            });
        }

        if (visibleBounds.length > 1) {
            map.fitBounds(visibleBounds, { padding: [60, 60], animate: true, maxZoom: 16 });
        }
    }

    window.applyFilter = updateFilters;

    window.resetFilter = function () {
        if (hargaSlider) hargaSlider.value = 90000;
        if (minRatingEl) minRatingEl.value  = 0;
        if (searchInput) searchInput.value  = '';
        activeFacFilters = [];
        starsEl.forEach(function (s) { s.classList.remove('active'); s.textContent = '☆'; });
        document.querySelectorAll('.kategori, .fasilitas').forEach(function (cb) { cb.checked = false; });
        document.querySelectorAll('.fac-item').forEach(function (el) { el.classList.remove('active'); });
        syncHargaLabel();
        updateFilters();
        map.setView(kampusCenter, 15);
    };

    window.toggleFacFilter = function (el, value) {
        el.classList.toggle('active');
        if (activeFacFilters.includes(value)) {
            activeFacFilters = activeFacFilters.filter(function (f) { return f !== value; });
        } else {
            activeFacFilters.push(value);
        }
        var cb = document.querySelector('.fasilitas[value="' + value + '"]');
        if (cb) cb.checked = el.classList.contains('active');
        updateFilters();
    };

    // Event listeners filter
    if (hargaSlider) hargaSlider.addEventListener('input', function () { syncHargaLabel(); updateFilters(); });
    starsEl.forEach(function (star) {
        star.addEventListener('click', function () {
            var val = parseInt(this.dataset.value, 10) || 0;
            if (minRatingEl) minRatingEl.value = val;
            starsEl.forEach(function (s) {
                var on = parseInt(s.dataset.value, 10) <= val;
                s.classList.toggle('active', on); s.textContent = on ? '★' : '☆';
            });
            updateFilters();
        });
    });
    document.querySelectorAll('.kategori, .fasilitas').forEach(function (cb) {
        cb.addEventListener('change', updateFilters);
    });
    if (searchInput) searchInput.addEventListener('input', updateFilters);

    /* ═══════════════════════════════════════════════════
       LAYER CONTROL & INIT
    ═══════════════════════════════════════════════════ */
    L.control.layers(null, {
        '📍 Tempat Nongkrong': pointLayer,
        '🏫 Kampus':           kampusLayer,
        '🚗 Rute / Layer':     routeLayer,
    }, { position: 'topright', collapsed: false }).addTo(map);

    createKampusMarker();
    syncHargaLabel();

    // Default mode: point (rute muncul saat klik marker)
    setLayerMode('point');
});