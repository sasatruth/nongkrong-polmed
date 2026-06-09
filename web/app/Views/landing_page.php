<?= view('layout/header'); ?>

<script>window.spatialData = <?= json_encode($spatial_data) ?>;</script>

<section class="container about-section text-center">
    <h2 class="section-title">Tentang Project GIS</h2>
    
    <div class="about-grid">
        <div class="about-item">
            <span class="icon about-icon">📊</span>
            <h4>Data Atribut</h4>
            <p>Fasilitas, harga, rating,<br>dan kategori tempat</p>
        </div>
        <div class="about-item">
            <span class="icon about-icon">📍</span>
            <h4>Data Spasial</h4>
            <p>Point, Line<br>+ Route dari kampus</p>
        </div>
        <div class="about-item">
            <span class="icon about-icon">🧭</span>
            <h4>Visualisasi</h4>
            <p>Peta interaktif +<br>perbandingan fasilitas</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <h3><?= $total_tempat ?></h3>
            <p>Total Tempat</p>
        </div>
        <div class="stat-item">
            <h3><?= round(($fasilitas_stats['wifi'] / max($total_tempat,1)) * 100, 1) ?>%</h3>
            <p>Ada Wifi</p>
        </div>
        <div class="stat-item">
            <h3><?= round(($fasilitas_stats['ac'] / max($total_tempat,1)) * 100, 1) ?>%</h3>
            <p>Ada AC</p>
        </div>
        <div class="stat-item">
            <h3><?= round(($fasilitas_stats['colokan'] / max($total_tempat,1)) * 100, 1) ?>%</h3>
            <p>Ada Colokan</p>
        </div>
    </div>

    <div class="about-box">
        <p>Analisis GIS lengkap dengan <strong>route lines dari kampus</strong> dan <strong>perbandingan fasilitas</strong> untuk membantu mahasiswa Polmed.</p>
    </div>
</section>

<section class="container facility-section" id="categories">
    <div class="text-center">
        <h2 class="section-title">Cari Berdasarkan Fasilitas</h2>
        <p class="section-subtitle">Filter tempat nongkrong sesuai kebutuhanmu</p>
    </div>
    
    <div class="fac-grid">
        <div class="fac-item" onclick="toggleFacFilter(this, 'wifi')">
            <span class="icon fac-icon">📶</span><p>Free Wifi</p>
        </div>
        <div class="fac-item" onclick="toggleFacFilter(this, 'ac')">
            <span class="icon fac-icon">❄️</span><p>AC</p>
        </div>
        <div class="fac-item" onclick="toggleFacFilter(this, 'colokan')">
            <span class="icon fac-icon">🔌</span><p>Colokan</p>
        </div>
        <div class="fac-item" onclick="toggleFacFilter(this, 'outdoor')">
            <span class="icon fac-icon">🪑</span><p>Outdoor Area</p>
        </div>
        <div class="fac-item" onclick="toggleFacFilter(this, 'musholla')">
            <span class="icon fac-icon">🕌</span><p>Musholla</p>
        </div>
        <div class="fac-item" onclick="toggleFacFilter(this, 'parkir_mobil')">
            <span class="icon fac-icon">🚗</span><p>Parkir Mobil</p>
        </div>
        <div class="fac-item" onclick="toggleFacFilter(this, 'toilet')">
            <span class="icon fac-icon">🚻</span><p>Toilet</p>
        </div>
    </div>
</section>

<section class="container dashboard-section" id="dashboard">
    <div class="text-center">
        <h2 class="section-title">GIS Dashboard</h2>
        <p class="section-subtitle">Point • Line dengan filter lengkap</p>
    </div>

    <div class="layer-toggle-bar text-center" style="margin-bottom:20px;">
        <button class="layer-btn active" id="btn-point"   onclick="setLayerMode('point')">📍 Point</button>
        <button class="layer-btn"        id="btn-line"    onclick="setLayerMode('line')">〰️ Line</button>
        <button class="layer-btn"        id="btn-all"     onclick="setLayerMode('all')">🗺️ Semua</button>
    </div>
    <p id="lineHint" style="text-align:center;font-size:0.78rem;color:#999;margin:-12px 0 18px;display:none;">
        💡 Klik marker atau tekan tombol rute untuk melihat rute dari lokasi Anda saat ini.
    </p>

    <div class="map-layout">
        <aside class="filter-sidebar" style="background: transparent; box-shadow: none; padding: 0; display: flex; flex-direction: column; gap: 16px;">
            
            <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1px solid #e2e2e2;">
                <h3 class="sidebar-title" style="margin-bottom: 12px;">📍 Lokasi Anda</h3>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button class="btn-focus-map" id="btn-gps" onclick="getLocationGPS()" style="width: 100%; text-align: center; white-space: normal;">
                        🛰️ Deteksi GPS Otomatis
                    </button>
                    <button class="btn-focus-map" id="btn-manual-pin" onclick="toggleManualPin()" style="width: 100%; text-align: center; white-space: normal;">
                        📍 Pin Manual di Peta
                    </button>
                </div>
                
                <div style="margin-top: 12px; padding: 10px; background: #f8f5f1; border-radius: 8px; border-left: 3px solid #007BFF;">
                    <p id="locationStatus" style="font-size: 0.78rem; color: #555; margin: 0; line-height: 1.4; font-family: 'Plus Jakarta Sans', sans-serif;">
                        Status: Menggunakan lokasi default (Polmed)
                    </p>
                </div>
            </div>

            <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1px solid #e2e2e2;">
                <h3 class="sidebar-title">Master Filters</h3>

                <div class="filter-group">
                    <h4>Maksimal Radius Jarak</h4>
                    <input type="range" id="radiusRange" min="0" max="10" step="0.5" value="0" class="price-slider">
                    <div class="price-labels">
                        <span>Semua</span>
                        <span id="radiusLabel">Tampilkan Semua</span>
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Minimum Rating</h4>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-value="1">☆</span>
                        <span class="star" data-value="2">☆</span>
                        <span class="star" data-value="3">☆</span>
                        <span class="star" data-value="4">☆</span>
                        <span class="star" data-value="5">☆</span>
                    </div>
                    <input type="hidden" id="minRating" value="0">
                </div>

                <div class="filter-group">
                    <h4>Range Harga Maksimal</h4>
                    <input type="range" id="harga" min="10000" max="90000" step="5000" value="90000" class="price-slider">
                    <div class="price-labels">
                        <span>Rp10.000</span>
                        <span id="hargaLabel">Rp90.000</span>
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Kategori Tempat</h4>
                    <label class="check-label"><input type="checkbox" class="kategori" value="kafe"> ☕ Kafe</label>
                    <label class="check-label"><input type="checkbox" class="kategori" value="warkop"> 🍵 Warkop</label>
                    <label class="check-label"><input type="checkbox" class="kategori" value="resto"> 🍽️ Resto</label>
                    <label class="check-label"><input type="checkbox" class="kategori" value="taman"> 🌳 Taman</label>
                </div>

                <div class="filter-group">
                    <h4>Fasilitas</h4>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="wifi"> 📶 Free Wifi</label>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="ac"> ❄️ AC</label>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="colokan"> 🔌 Colokan</label>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="outdoor"> 🪑 Outdoor Area</label>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="musholla"> 🕌 Musholla</label>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="parkir_mobil"> 🚗 Parkir Mobil</label>
                    <label class="check-label"><input type="checkbox" class="fasilitas" value="toilet"> 🚻 Toilet</label>
                </div>

                <button class="btn-reset" onclick="resetFilter()" style="width:100%; margin-top:10px;">Reset</button>
            </div>
        </aside>

        <div class="map-wrapper">
            <div id="map"></div>

            <div class="map-legend">
                <h4>Legenda GIS</h4>
                
                <div class="legend-header-row">
                    <span class="hdr-label">Kategori</span>
                    <span class="hdr-sym">Pt</span>
                    <span class="hdr-sym">Ln</span>
                </div>

                <div class="legend-items">
                    <div class="legend-item">
                        <span class="legend-label">Kafe</span>
                        <span class="legend-icon badge-kafe">☕</span>
                        <span class="legend-line line-kafe"></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-label">Warkop</span>
                        <span class="legend-icon badge-warkop">🍵</span>
                        <span class="legend-line line-warkop"></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-label">Resto</span>
                        <span class="legend-icon badge-resto">🍽️</span>
                        <span class="legend-line line-resto"></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-label">Taman</span>
                        <span class="legend-icon badge-taman">🌳</span>
                        <span class="legend-line line-taman"></span>
                    </div>
                </div>

                <div class="legend-footer-item">
                    <span class="legend-icon badge-kampus" style="background:#2D6A4F; font-size:10px; width:20px; height:20px;">🏫</span>
                    <span class="legend-label" style="font-weight:normal; font-size:0.75rem;">Kampus Polmed</span>
                </div>
                <div class="legend-footer-item" style="margin-top: 4px; padding-top: 4px; border: none;">
                    <span class="legend-icon" style="background:#007BFF; font-size:10px; width:20px; height:20px;">👤</span>
                    <span class="legend-label" style="font-weight:normal; font-size:0.75rem;">Lokasi Anda (Bisa Digeser)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center" style="margin-top:50px;">
        <h2 class="section-title">Tempat yang Direkomendasikan</h2>
        <p class="section-subtitle">Hasil pencarian berdasarkan kriteria spasial Anda</p>
    </div>

    <div class="recommended-grid" id="placesGrid">
        <?php foreach ($tempat_nongkrong as $t): ?>
        <?php
            $fasilitasList = [];
            if (!empty($t['wifi']))         $fasilitasList[] = 'wifi';
            if (!empty($t['ac']))           $fasilitasList[] = 'ac';
            if (!empty($t['colokan']))      $fasilitasList[] = 'colokan';
            if (!empty($t['outdoor']))      $fasilitasList[] = 'outdoor';
            if (!empty($t['musholla']))     $fasilitasList[] = 'musholla';
            if (!empty($t['parkir_mobil'])) $fasilitasList[] = 'parkir_mobil';
            if (!empty($t['toilet']))       $fasilitasList[] = 'toilet';
            $fasilitasStr = implode(',', $fasilitasList);

            $imgUrl = !empty($t['foto_url']) ? $t['foto_url'] : '';
            $kat = strtolower(trim($t['kategori'] ?? 'kafe'));
            $gradients = [
                'kafe'   => 'linear-gradient(135deg,#B08968,#7A553A)',
                'warkop' => 'linear-gradient(135deg,#8B6F47,#5C3D1E)',
                'resto'  => 'linear-gradient(135deg,#A0785A,#6B4226)',
                'taman'  => 'linear-gradient(135deg,#6B8F5E,#3D5E35)',
            ];
            $bgStyle = $imgUrl
                ? "background-image:url('" . htmlspecialchars($imgUrl) . "');"
                : "background:" . ($gradients[$kat] ?? $gradients['kafe']) . ";";

            $lat = (float)($t['latitude']  ?? 0);
            $lng = (float)($t['longitude'] ?? 0);
            $id_tempat = $t['id'] ?? 0;
        ?>
        <article class="place-card"
            data-id="<?= $id_tempat ?>"
            data-lat="<?= $lat ?>"
            data-lng="<?= $lng ?>"
            data-harga-min="<?= (int)($t['harga_min'] ?? 0) ?>"
            data-harga="<?= (int)($t['harga_max'] ?? 0) ?>"
            data-rating="<?= (float)($t['rating'] ?? 0) ?>"
            data-kategori="<?= htmlspecialchars($kat) ?>"
            data-fasilitas="<?= $fasilitasStr ?>"
            data-nama="<?= htmlspecialchars($t['nama_tempat'] ?? '') ?>"
            data-alamat="<?= htmlspecialchars($t['alamat'] ?? '') ?>"
            data-jam="<?= htmlspecialchars($t['jam_buka'] ?? '') ?>"
            data-kontak="<?= htmlspecialchars($t['kontak_ig_wa'] ?? '') ?>"
            data-catatan="<?= htmlspecialchars($t['catatan'] ?? '') ?>">

            <div class="card-img" style="<?= $bgStyle ?> background-size:cover; background-position:center; position:relative;">
                <?php if (!$imgUrl): ?>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:3rem;opacity:0.35;">
                        <?php $icons = ['kafe'=>'☕','warkop'=>'🍵','resto'=>'🍽️','taman'=>'🌳']; echo $icons[$kat] ?? '☕'; ?>
                    </div>
                <?php endif; ?>
                <span class="card-tag"><?= htmlspecialchars($kat) ?></span>
            </div>

            <div class="card-content">
                <div class="card-header-row">
                    <h3><?= htmlspecialchars($t['nama_tempat'] ?? '') ?></h3>
                    <div class="card-rating">⭐ <?= $t['rating'] ?? '-' ?></div>
                </div>
                <p class="card-price">
                    Rp <?= number_format($t['harga_min'] ?? 0, 0, ',', '.') ?>
                    &ndash;
                    <?= number_format($t['harga_max'] ?? 0, 0, ',', '.') ?>
                </p>
                <div class="card-fac-list">
                    <?= !empty($t['wifi'])         ? '<span>📶 Wifi</span>'        : '' ?>
                    <?= !empty($t['ac'])            ? '<span>❄️ AC</span>'          : '' ?>
                    <?= !empty($t['colokan'])       ? '<span>🔌 Colokan</span>'     : '' ?>
                    <?= !empty($t['outdoor'])       ? '<span>🪑 Outdoor</span>'     : '' ?>
                    <?= !empty($t['musholla'])      ? '<span>🕌 Musholla</span>'    : '' ?>
                    <?= !empty($t['parkir_mobil'])  ? '<span>🚗 Parkir</span>'      : '' ?>
                    <?= !empty($t['toilet'])        ? '<span>🚻 Toilet</span>'      : '' ?>
                </div>

                <div class="card-btn-row">
                    <button class="btn-focus-map"
                        onclick="focusMarker(<?= $lat ?>, <?= $lng ?>, '<?= htmlspecialchars($t['nama_tempat'] ?? '') ?>')">
                                📍 Lihat di Peta
                            </button>
                    <a href="<?= base_url('tempat/' . $id_tempat) ?>" class="btn-detail">
                        🔍 Detail & Ulasan
                    </a>
                </div>
                
                <div style="margin-top: 8px;">
                    <button class="btn-detail" style="background: #007BFF; width: 100%; border: none; cursor: pointer;" 
                        onclick="generateDynamicRoute(<?= $lat ?>, <?= $lng ?>, '<?= htmlspecialchars($t['nama_tempat'] ?? '') ?>')">
                        🚗 Hitung Rute dari Lokasi Saya
                    </button>
                </div>

            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<style>
/* Layout Container Peta */
.map-wrapper {
    position: relative;
    flex: 1;
    min-height: 500px;
}

#map {
    width: 100%;
    height: 100%;
    border-radius: 8px;
}

/* Kotak Legenda Melayang Pro GIS */
.map-legend {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.96);
    padding: 12px 14px;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-width: 220px;
    border: 1px solid #e2e2e2;
    pointer-events: auto;
}

.map-legend h4 {
    margin: 0 0 8px 0;
    font-size: 0.82rem;
    font-weight: 700;
    color: #333;
    border-bottom: 2px solid #B08968;
    padding-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Kolom Header Legenda */
.legend-header-row {
    display: flex;
    align-items: center;
    font-size: 0.68rem;
    font-weight: 700;
    color: #888;
    margin-bottom: 6px;
    padding-bottom: 2px;
    border-bottom: 1px dashed #eee;
}

.hdr-label { flex: 1; }
.hdr-sym { width: 30px; text-align: center; }

.legend-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.legend-item {
    display: flex;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: #444;
}

.legend-label {
    flex: 1;
}

.legend-icon {
    font-size: 11px;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    margin: 0 4px;
}

.legend-line {
    width: 22px;
    height: 4px;
    border-radius: 2px;
    margin: 0 4px;
}

.badge-kafe   { background-color: #FF6B35; }
.line-kafe    { background-color: #FF6B35; }

.badge-warkop { background-color: #4ECDC4; }
.line-warkop  { background-color: #4ECDC4; }

.badge-resto  { background-color: #45B7D1; }
.line-resto   { background-color: #45B7D1; }

.badge-taman  { background-color: #96CEB4; }
.line-taman   { background-color: #96CEB4; }

.legend-footer-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    padding-top: 6px;
    border-top: 1px dashed #eee;
}

/* Layer Toggle Bar */
.layer-toggle-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 20px;
}
.layer-btn {
    padding: 8px 20px;
    border: 2px solid #B08968;
    background: transparent;
    color: #B08968;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .25s;
    margin: 2px;
}
.layer-btn:hover { background: #B0896822; }
.layer-btn.active {
    background: #B08968;
    color: #fff;
}

.card-btn-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 12px;
}

.btn-focus-map {
    background: var(--bg-latte);
    border: 1px solid var(--warm-cappuccino);
    color: var(--espresso);
    padding: 10px 8px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.8rem;
    cursor: pointer;
    transition: 0.2s;
    font-family: 'Plus Jakarta Sans', sans-serif;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.btn-focus-map:hover { background: var(--warm-cappuccino); }

.btn-detail {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--espresso);
    color: var(--bg-latte);
    padding: 10px 8px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.8rem;
    text-decoration: none;
    transition: background 0.2s, transform 0.15s;
    font-family: 'Plus Jakarta Sans', sans-serif;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
}
.btn-detail:hover {
    background: var(--mocha);
    color: var(--bg-latte);
    transform: translateY(-1px);
}
</style>

<script>
const KAT_COLORS = {
    kafe:   { marker: '#FF6B35', line: '#FF6B35' },
    warkop: { marker: '#4ECDC4', line: '#4ECDC4' },
    resto:  { marker: '#45B7D1', line: '#45B7D1' },
    taman:  { marker: '#96CEB4', line: '#96CEB4' },
};
const DEFAULT_COLOR = '#B08968';

function normalizeKat(raw) {
    if (!raw) return 'kafe';
    const s = raw.toString().toLowerCase().trim().replace(/\s+/g, '');
    if (s.includes('warkop')) return 'warkop';
    if (s.includes('resto'))  return 'resto';
    if (s.includes('taman'))  return 'taman';
    if (s.includes('kafe'))   return 'kafe';
    return 'kafe';
}

function getColor(kat, type) {
    return (KAT_COLORS[kat] || {})[type] || DEFAULT_COLOR;
}

// ── State ───────────────────────────────────────────────────────────────────
let map;
let markerLayers     = {};
let lineLayers       = {};
let currentMode      = 'point';
let activeFacFilters = [];
let routesReady      = false;   
let selectedMarkerIdx = null;   

// Variabel Lokasi, Radius, dan Keyword Search Navbar
let currentUserLatLng = [3.5626, 98.6569]; 
let userMarker = null;
let dynamicRouteLine = null;
let dynamicRoutePopup = null; 
let userRadiusCircle = null; 
let isManualPinMode = false;
let searchKeyword = '';

async function fetchRoute(fromLatLng, toLatLng) {
    const [fLat, fLng] = fromLatLng;
    const [tLat, tLng] = toLatLng;
    const url = `https://router.project-osrm.org/route/v1/driving/`
              + `${fLng},${fLat};${tLng},${tLat}`
              + `?overview=full&geometries=geojson`;
    try {
        const res  = await fetch(url);
        const data = await res.json();
        if (data.code !== 'Ok' || !data.routes || !data.routes.length) return null;
        return data.routes[0].geometry.coordinates.map(([lng, lat]) => [lat, lng]);
    } catch {
        return null;   
    }
}

// ── Init Map ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const sd = window.spatialData;
    const center = sd.kampus_center; 

    map = L.map('map').setView(center, 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker(center, {
        icon: L.divIcon({
            html: '<div style="background:#2D6A4F;color:#fff;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap;">🏫 Kampus Polmed</div>',
            className: '', iconAnchor:[40,12]
        })
    }).addTo(map);

    userMarker = L.marker(currentUserLatLng, {
        draggable: true,
        icon: L.divIcon({
            html: '<div style="background:#007BFF;color:#fff;padding:6px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,0.3)">👤 Lokasi Anda</div>',
            className: '', iconAnchor:[35,12]
        })
    }).addTo(map);

    userMarker.on('dragend', function (e) {
        const position = userMarker.getLatLng();
        updateUserLocationState([position.lat, position.lng], "Dipilih manual via geser marker");
    });

    map.on('click', function(e) {
        if(isManualPinMode) {
            updateUserLocationState([e.latlng.lat, e.latlng.lng], "Dipilih manual via klik peta");
            toggleManualPin(); 
        }
    });

    const points = (sd.points || []).filter(p => {
        const lat = parseFloat(p.latitude);
        const lng = parseFloat(p.longitude);
        return !isNaN(lat) && !isNaN(lng)
            && Math.abs(lat) > 0.001
            && Math.abs(lng) > 0.001
            && lat >= -11 && lat <= 6
            && lng >= 95 && lng <= 141;
    });

    points.forEach((p, i) => {
        const kat  = normalizeKat(p.kategori);
        const col  = getColor(kat, 'marker');
        const lat  = parseFloat(p.latitude);
        const lng  = parseFloat(p.longitude);

        const icons = { kafe:'☕', warkop:'🍵', resto:'🍽️', taman:'🌳' };
        const m = L.marker([lat, lng], {
            icon: L.divIcon({
                html: `<div style="
                    background:${col};
                    color:#fff;
                    width:34px;height:34px;
                    border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);
                    display:flex;align-items:center;justify-content:center;
                    border:2px solid #fff;
                    box-shadow:0 2px 6px rgba(0,0,0,.35);
                    font-size:14px;
                "><span style="transform:rotate(45deg)">${icons[kat]||'📍'}</span></div>`,
                className: '',
                iconSize: [34,34],
                iconAnchor: [17,34],
                popupAnchor: [0,-36]
            })
        });

        m.bindPopup(`
            <div style="min-width:220px;font-family:Poppins,sans-serif;">
                <strong style="font-size:15px;">${p.nama_tempat || '-'}</strong><br>
                <span style="color:#888;font-size:.8rem">${kat}</span><br>
                ⭐ ${p.rating || '-'} &nbsp;|&nbsp; Rp ${Number(p.harga_min||0).toLocaleString('id')} – ${Number(p.harga_max||0).toLocaleString('id')}<br>
                📍 ${p.alamat || '-'}<br><br>
                <div style="background:#f8f5f1; padding:8px; border-radius:8px; font-size:.82rem; line-height:1.6;">
                    <b>Fasilitas:</b><br>
                    ${p.wifi ? '📶 Free Wifi<br>' : ''}
                    ${p.ac ? '❄️ AC<br>' : ''}
                    ${p.colokan ? '🔌 Colokan<br>' : ''}
                    ${p.outdoor ? '🌿 Outdoor Area<br>' : ''}
                    ${p.musholla ? '🕌 Musholla<br>' : ''}
                    ${p.parkir_mobil ? '🚗 Parkir Mobil<br>' : ''}
                    ${p.toilet ? '🚻 Toilet<br>' : ''}
                </div>
                <a href="<?= base_url('tempat/') ?>${p.id || 0}" style="display:block; background:#3B2A22; color:#F3E9D7; text-align:center; padding:8px 12px; border-radius:8px; font-size:.8rem; font-weight:700; text-decoration:none; margin-top:10px;">
                    🔍 Detail & Ulasan
                </a>
                <button onclick="generateDynamicRoute(${lat}, ${lng}, '${(p.nama_tempat || '').replace(/'/g, "\\'")}')" style="display:block; width:100%; background:#007BFF; color:#fff; text-align:center; padding:8px 12px; border-radius:8px; font-size:.8rem; font-weight:700; border:none; margin-top:6px; cursor:pointer;">
                    🚗 Rute dari Lokasi Saya
                </button>
            </div>
        `);

        markerLayers[i] = { layer: m, lat, lng, kat,
            harga: p.harga_max||0, rating: p.rating||0,
            fasilitas: [
                p.wifi?'wifi':'', p.ac?'ac':'', p.colokan?'colokan':'',
                p.outdoor?'outdoor':'', p.musholla?'musholla':'',
                p.parkir_mobil?'parkir_mobil':'', p.toilet?'toilet':''
            ].filter(Boolean),
            lineRef: null
        };

        m.on('click', () => {
            if (currentMode !== 'line' && currentMode !== 'all') return;
            if (selectedMarkerIdx === i) {
                selectedMarkerIdx = null;
            } else {
                selectedMarkerIdx = i;
            }
            applyFilter();
            if (selectedMarkerIdx === i) {
                setTimeout(() => m.openPopup(), 10);
            }
        });
    });

    const routeLoadingEl = document.createElement('div');
    routeLoadingEl.id = 'routeLoading';
    routeLoadingEl.style.cssText = [
        'position:fixed', 'bottom:24px', 'right:24px',
        'background:rgba(43,27,18,.88)', 'color:#F3E9D7',
        'padding:10px 18px', 'border-radius:10px',
        'font-size:13px', 'font-weight:600',
        'z-index:9999', 'display:none',
        'box-shadow:0 4px 14px rgba(0,0,0,.35)',
        'backdrop-filter:blur(4px)'
    ].join(';');
    document.body.appendChild(routeLoadingEl);

    async function buildAllRoutes() {
        const entries = Object.entries(markerLayers);
        if (!entries.length) return;

        routeLoadingEl.style.display = 'block';
        routeLoadingEl.textContent   = `⏳ Memuat rute 0/${entries.length}...`;

        let done = 0;
        const BATCH = 3;   

        for (let i = 0; i < entries.length; i += BATCH) {
            const batch = entries.slice(i, i + BATCH);
            await Promise.all(batch.map(async ([idx, o]) => {
                const coords = await fetchRoute(center, [o.lat, o.lng]);
                const col    = getColor(o.kat, 'line');

                const popupContent = o.layer.getPopup() ? o.layer.getPopup().getContent() : '';
                const namaMatch    = popupContent.match(/<strong>(.*?)<\/strong>/);
                const nama         = namaMatch ? namaMatch[1] : o.kat;

                const pl = coords
                    ? L.polyline(coords, { color: col, weight: 4, opacity: 0.85 })
                    : L.polyline([center, [o.lat, o.lng]], { color: col, weight: 3, opacity: 0.6, dashArray: '8,5' });

                pl.bindTooltip(`🛣️ Rute dari Polmed ke ${nama}`, { sticky: true });

                if (!lineLayers[o.kat]) lineLayers[o.kat] = [];
                lineLayers[o.kat].push(pl);
                o.lineRef = pl;

                done++;
                routeLoadingEl.textContent = `⏳ Memuat rute ${done}/${entries.length}...`;
            }));
        }

        routesReady = true;
        routeLoadingEl.textContent = '✅ Semua rute siap!';
        setTimeout(() => { routeLoadingEl.style.display = 'none'; }, 2500);

        if (currentMode === 'line' || currentMode === 'all') applyFilter();
    }

    buildAllRoutes();

    setLayerMode('point');
});

// ── MANAJEMEN LIVE SEARCH NAVBAR ─────────────────────────────────────────────────────
function liveSearch() {
    const input = document.getElementById('searchInput');
    if (input) {
        searchKeyword = input.value.toLowerCase().trim();
        applyFilter();
    }
}

// ── MANAJEMEN LOKASI, RADIUS, DAN RUTE ──────────────────────────────────────────────
function updateUserLocationState(latLng, methodText) {
    currentUserLatLng = latLng;
    userMarker.setLatLng(latLng);
    document.getElementById('locationStatus').textContent = `Status: Terdeteksi (${methodText}) [${latLng[0].toFixed(4)}, ${latLng[1].toFixed(4)}]`;
    
    const radiusVal = parseFloat(document.getElementById('radiusRange').value);
    if (radiusVal > 0) {
        drawRadiusCircle(radiusVal);
    }

    if(dynamicRouteLine && map.hasLayer(dynamicRouteLine)) {
        const currentDest = dynamicRouteLine.options.destLatLng;
        const currentDestName = dynamicRouteLine.options.destName;
        if(currentDest) {
            generateDynamicRoute(currentDest[0], currentDest[1], currentDestName, false);
        }
    } else {
        applyFilter();
    }
}

function drawRadiusCircle(radiusKm) {
    if (userRadiusCircle) {
        map.removeLayer(userRadiusCircle);
    }
    userRadiusCircle = L.circle(currentUserLatLng, {
        radius: radiusKm * 1000,
        color: '#007BFF',
        fillColor: '#007BFF',
        fillOpacity: 0.08,
        weight: 1.5,
        dashArray: '4, 4'
    }).addTo(map);
}

function getLocationGPS() {
    if (navigator.geolocation) {
        document.getElementById('locationStatus').textContent = "⏳ Meminta izin GPS lokasi gawai...";
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latLng = [position.coords.latitude, position.coords.longitude];
                updateUserLocationState(latLng, "GPS Gawai Otomatis");
                map.setView(latLng, 14);
            },
            (error) => {
                alert("Gagal mendeteksi lokasi otomatis. Pastikan setelan izin lokasi browser aktif.");
                document.getElementById('locationStatus').textContent = "⚠️ Gagal mengakses GPS.";
            }
        );
    } else {
        alert("Browser Anda tidak mendukung fitur Geolocation.");
    }
}

function toggleManualPin() {
    isManualPinMode = !isManualPinMode;
    const btn = document.getElementById('btn-manual-pin');
    if (isManualPinMode) {
        btn.style.background = "#007BFF";
        btn.style.color = "#fff";
        btn.textContent = "🎯 Silakan Klik di Peta...";
        document.getElementById('map').style.cursor = 'crosshair';
    } else {
        btn.style.background = "";
        btn.style.color = "";
        btn.textContent = "📍 Pin Manual di Peta";
        document.getElementById('map').style.cursor = '';
    }
}

async function generateDynamicRoute(destLat, destLng, destName, autoScroll = true) {
    if (dynamicRouteLine) map.removeLayer(dynamicRouteLine);
    if (dynamicRoutePopup) map.closePopup(dynamicRoutePopup);

    const routeLoadingEl = document.getElementById('routeLoading');
    if (routeLoadingEl) {
        routeLoadingEl.style.display = 'block';
        routeLoadingEl.textContent = `⏳ Menghitung rute ke ${destName}...`;
    }

    const coords = await fetchRoute(currentUserLatLng, [destLat, destLng]);
    if (routeLoadingEl) routeLoadingEl.style.display = 'none';

    const jarak = hitungJarak(currentUserLatLng[0], currentUserLatLng[1], destLat, destLng);
    const jarakText = jarak < 1 ? (jarak * 1000).toFixed(0) + ' meter' : jarak.toFixed(2) + ' km';

    const popupContent = `
        <div style="font-family:'Plus Jakarta Sans',sans-serif; text-align:center; padding: 2px;">
            <b style="color:#007BFF; font-size:13px;">🚗 Jalur Rute Aktif</b><br>
            <span style="font-size:12px; color:#333;">Jarak: <b>${jarakText}</b> menuju ${destName}</span>
        </div>
    `;

    if (coords) {
        dynamicRouteLine = L.polyline(coords, {
            color: '#007BFF', 
            weight: 6,
            opacity: 0.9,
            destLatLng: [destLat, destLng], 
            destName: destName
        }).addTo(map);

        const centerIndex = Math.floor(coords.length / 2);
        const midCoordinate = coords[centerIndex];

        dynamicRoutePopup = L.popup({ closeOnClick: false, autoClose: false })
            .setLatLng(midCoordinate)
            .setContent(popupContent)
            .openOn(map);
        
        if (autoScroll) {
            const mapEl = document.getElementById('map');
            if (mapEl) mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            map.fitBounds(dynamicRouteLine.getBounds(), { padding: [60, 60] });
        }
    } else {
        const straightLineCoords = [currentUserLatLng, [destLat, destLng]];
        dynamicRouteLine = L.polyline(straightLineCoords, {
            color: '#007BFF', weight: 4, dashArray: '10, 10',
            destLatLng: [destLat, destLng], destName: destName
        }).addTo(map);

        const midCoordinate = [
            (currentUserLatLng[0] + destLat) / 2,
            (currentUserLatLng[1] + destLng) / 2
        ];

        dynamicRoutePopup = L.popup({ closeOnClick: false, autoClose: false })
            .setLatLng(midCoordinate)
            .setContent(popupContent)
            .openOn(map);
    }
}

// ── SISTEM FILTER CORE UTAMA ──────────────────────────────────────────────────
function setLayerMode(mode) {
    currentMode = mode;
    ['point','line','all'].forEach(m => {
        document.getElementById('btn-'+m)?.classList.toggle('active', m === mode);
    });

    selectedMarkerIdx = null;
    Object.values(markerLayers).forEach(mo => {
        const el = mo.layer.getElement();
        if (el) el.style.opacity = '1';
    });

    const hint = document.getElementById('lineHint');
    if (hint) hint.style.display = (mode === 'line' || mode === 'all') ? 'block' : 'none';

    if ((mode === 'line' || mode === 'all') && !routesReady) {
        const el = document.getElementById('routeLoading');
        if (el) el.style.display = 'block';
    }

    applyFilter();
}

function applyFilter() {
    const radiusMax = parseFloat(document.getElementById('radiusRange').value || 0);
    const minRating = parseFloat(document.getElementById('minRating').value || 0);
    const maxHarga  = parseInt(document.getElementById('harga').value || 90000);
    const kats = [...document.querySelectorAll('.kategori:checked')].map(c => normalizeKat(c.value));
    const facs      = [...document.querySelectorAll('.fasilitas:checked')].map(c => c.value);

    Object.values(markerLayers).forEach(o => map.removeLayer(o.layer));
    Object.values(lineLayers).flat().forEach(l => map.removeLayer(l));

    const passedLinesByKat  = {};

    Object.entries(markerLayers).forEach(([idx, o]) => {
        const popupContent = o.layer.getPopup() ? o.layer.getPopup().getContent() : '';
        const namaMatch    = popupContent.match(/<strong>(.*?)<\/strong>/);
        const namaTempat   = namaMatch ? namaMatch[1].toLowerCase() : '';

        // Logika Filter Spasial Jarak Radius & Live Search Navbar
        const jarakKeUser = hitungJarak(currentUserLatLng[0], currentUserLatLng[1], o.lat, o.lng);
        const radiusOk   = radiusMax === 0 || jarakKeUser <= radiusMax;
        const searchOk   = searchKeyword === '' || namaTempat.includes(searchKeyword);

        const katOk      = kats.length === 0 || kats.includes(o.kat);
        const hargaOk    = o.harga <= maxHarga;
        const ratingOk   = o.rating >= minRating;
        const facOk      = facs.length === 0 || facs.every(f => o.fasilitas.includes(f));
        const facQuickOk = activeFacFilters.length === 0 || activeFacFilters.every(f => o.fasilitas.includes(f));
        
        const passed     = radiusOk && searchOk && katOk && hargaOk && ratingOk && facOk && facQuickOk;

        if (passed) {
            if (currentMode === 'point' || currentMode === 'all') {
                o.layer.addTo(map);
            }
            if (!passedLinesByKat[o.kat]) passedLinesByKat[o.kat] = [];
            if (o.lineRef) passedLinesByKat[o.kat].push(o.lineRef);
        }
    });

    if (currentMode === 'line' || currentMode === 'all') {
        if (selectedMarkerIdx !== null && markerLayers[selectedMarkerIdx]) {
            const o = markerLayers[selectedMarkerIdx];
            if (o.lineRef) o.lineRef.addTo(map);

            Object.entries(markerLayers).forEach(([idx, mo]) => {
                const el = mo.layer.getElement();
                if (!el) return;
                el.style.opacity = (parseInt(idx) === selectedMarkerIdx) ? '1' : '0.25';
            });
        } else {
            Object.values(passedLinesByKat).flat().forEach(l => l.addTo(map));
            Object.values(markerLayers).forEach(mo => {
                const el = mo.layer.getElement();
                if (el) el.style.opacity = '1';
            });
        }
    } else {
        selectedMarkerIdx = null;
        Object.values(markerLayers).forEach(mo => {
            const el = mo.layer.getElement();
            if (el) el.style.opacity = '1';
        });
    }

    filterCards(kats, maxHarga, minRating, facs, radiusMax);
}

function resetFilter() {
    document.getElementById('radiusRange').value = 0;
    document.getElementById('radiusLabel').textContent = 'Tampilkan Semua';
    document.getElementById('minRating').value = 0;
    document.getElementById('harga').value = 90000;
    document.getElementById('hargaLabel').textContent = 'Rp90.000';
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    searchKeyword = '';

    document.querySelectorAll('.kategori, .fasilitas').forEach(c => c.checked = false);
    document.querySelectorAll('.star').forEach(s => s.textContent = '☆');
    activeFacFilters = [];
    selectedMarkerIdx = null;
    
    if (dynamicRouteLine) { map.removeLayer(dynamicRouteLine); dynamicRouteLine = null; }
    if (dynamicRoutePopup) { map.closePopup(dynamicRoutePopup); dynamicRoutePopup = null; }
    if (userRadiusCircle) { map.removeLayer(userRadiusCircle); userRadiusCircle = null; }
    
    currentUserLatLng = [3.5626, 98.6569];
    userMarker.setLatLng(currentUserLatLng);
    document.getElementById('locationStatus').textContent = "Status: Menggunakan lokasi default (Polmed)";
    document.querySelectorAll('.fac-item').forEach(el => el.classList.remove('active'));
    
    Object.values(markerLayers).forEach(mo => {
        const el = mo.layer.getElement();
        if (el) el.style.opacity = '1';
    });
    setLayerMode(currentMode);
    document.querySelectorAll('.place-card').forEach(c => c.style.display = '');
}

function toggleFacFilter(el, fac) {
    el.classList.toggle('active');
    if (activeFacFilters.includes(fac)) {
        activeFacFilters = activeFacFilters.filter(f => f !== fac);
    } else {
        activeFacFilters.push(fac);
    }
    applyFilter();
}

function filterCards(kats, maxHarga, minRating, facs, radiusMax) {
    document.querySelectorAll('.place-card').forEach(card => {
        const lat    = parseFloat(card.dataset.lat || 0);
        const lng    = parseFloat(card.dataset.lng || 0);
        const namaCard = (card.dataset.nama || '').toLowerCase();
        const jarakKeUser = hitungJarak(currentUserLatLng[0], currentUserLatLng[1], lat, lng);
        
        const radiusOk = radiusMax === 0 || jarakKeUser <= radiusMax;
        const searchOk = searchKeyword === '' || namaCard.includes(searchKeyword);
        const kat    = normalizeKat(card.dataset.kategori);
        const harga  = parseInt(card.dataset.harga || 0);
        const rating = parseFloat(card.dataset.rating || 0);
        const cardFacs = (card.dataset.fasilitas || '').split(',');
        const katOk  = kats.length === 0 || kats.includes(kat);
        const hargaOk = harga <= maxHarga;
        const ratingOk = rating >= minRating;
        const facOk  = facs.length === 0 || facs.every(f => cardFacs.includes(f));
        const facQOk = activeFacFilters.length === 0 || activeFacFilters.every(f => cardFacs.includes(f));
        
        card.style.display = (radiusOk && searchOk && katOk && hargaOk && ratingOk && facOk && facQOk) ? '' : 'none';
    });
}

function hitungJarak(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2)
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
            * Math.sin(dLng/2) * Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function focusMarker(lat, lng, namaTempat) {
    const mapEl = document.getElementById('map');
    if (mapEl) mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

    setTimeout(() => {
        map.setView([lat, lng], 16);
        Object.values(markerLayers).forEach(o => {
            if (Math.abs(o.lat - lat) < 0.00001 && Math.abs(o.lng - lng) < 0.00001) {
                o.layer.openPopup();
            }
        });

        const jarak = hitungJarak(currentUserLatLng[0], currentUserLatLng[1], lat, lng);
        const jarakText = jarak < 1 ? (jarak * 1000).toFixed(0) + ' m' : jarak.toFixed(2) + ' km';

        let infoBox = document.getElementById('jarak-info');
        if (!infoBox) {
            infoBox = document.createElement('div');
            infoBox.id = 'jarak-info';
            infoBox.style.cssText = `
                position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
                background: rgba(30,20,10,0.88); color: #F5E6D3;
                padding: 10px 20px; border-radius: 20px; font-size: 0.9rem;
                font-weight: 600; z-index: 1000; pointer-events: none;
                box-shadow: 0 4px 166px rgba(0,0,0,0.3); white-space: nowrap;
            `;
            document.getElementById('map').style.position = 'relative';
            document.getElementById('map').appendChild(infoBox);
        }
        infoBox.textContent = `📍 Lokasi Anda → ${namaTempat}: ${jarakText}`;
        infoBox.style.opacity = '1';

        clearTimeout(infoBox._timer);
        infoBox._timer = setTimeout(() => { infoBox.style.opacity = '0'; }, 5000);
    }, 500);
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('radiusRange').addEventListener('input', function() {
        const val = parseFloat(this.value);
        if (val === 0) {
            document.getElementById('radiusLabel').textContent = 'Tampilkan Semua';
            if (userRadiusCircle) { map.removeLayer(userRadiusCircle); userRadiusCircle = null; }
        } else {
            document.getElementById('radiusLabel').textContent = val + ' km';
            drawRadiusCircle(val);
        }
        applyFilter();
    });

    document.getElementById('harga').addEventListener('input', function() {
        document.getElementById('hargaLabel').textContent = 'Rp' + parseInt(this.value).toLocaleString('id');
        applyFilter();
    });

    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', () => {
            const val = parseInt(star.dataset.value);
            document.getElementById('minRating').value = val;
            document.querySelectorAll('.star').forEach((s, i) => {
                s.textContent = i < val ? '★' : '☆';
            });
            applyFilter();
        });
    });

    document.querySelectorAll('.kategori').forEach(cb => { cb.addEventListener('change', () => applyFilter()); });
    document.querySelectorAll('.fasilitas').forEach(cb => { cb.addEventListener('change', () => applyFilter()); });
});
</script>

<?= view('layout/footer'); ?>