<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tempat['nama_tempat'] ?? 'Detail Tempat') ?> | Nongkrong Polmed</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* ══════════════════════════════════════════
           DETAIL PAGE — extra styles
        ══════════════════════════════════════════ */

        /* ── Breadcrumb ── */
        .breadcrumb {
            background: var(--espresso);
            padding: 14px 10%;
            font-size: 0.82rem;
            color: var(--warm-cappuccino);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .breadcrumb a { color: var(--warm-cappuccino); transition: color .2s; }
        .breadcrumb a:hover { color: var(--bg-latte); }
        .breadcrumb span { opacity: .5; }

        /* ── Hero Detail ── */
        .detail-hero {
            position: relative;
            height: 420px;
            background: var(--espresso);
            overflow: hidden;
        }
        .detail-hero-img {
            width: 100%; height: 100%;
            object-fit: cover;
            opacity: .7;
        }
        .detail-hero-placeholder {
            width: 100%; height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7rem;
            opacity: .18;
        }
        .detail-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(59,42,34,.92) 0%, rgba(59,42,34,.2) 60%, transparent 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 48px 10%;
        }
        .detail-kat-badge {
            display: inline-block;
            background: var(--caramel);
            color: var(--white);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 30px;
            margin-bottom: 14px;
        }
        .detail-hero-title {
            font-family: 'Lora', serif;
            font-size: 2.8rem;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 12px;
        }
        .detail-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            color: var(--warm-cappuccino);
            font-size: .9rem;
        }
        .detail-hero-meta span { display: flex; align-items: center; gap: 6px; }

        /* ── Layout Body ── */
        .detail-body {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 40px;
            padding: 60px 10%;
            align-items: start;
        }

        /* ── Info Card ── */
        .info-card {
            background: var(--white);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 8px 32px rgba(59,42,34,.1);
            margin-bottom: 32px;
        }
        .info-card-title {
            font-family: 'Lora', serif;
            font-size: 1.15rem;
            color: var(--espresso);
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--bg-latte);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Fasilitas chips */
        .fac-chips { display: flex; flex-wrap: wrap; gap: 10px; }
        .fac-chip {
            background: var(--bg-latte);
            color: var(--espresso);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: .82rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .fac-chip.off {
            opacity: .35;
            text-decoration: line-through;
            background: #eee;
        }

        /* Info rows */
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid var(--bg-latte);
            font-size: .9rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
        .info-row-label { font-weight: 700; color: var(--mocha); margin-bottom: 2px; font-size: .75rem; text-transform: uppercase; letter-spacing: .6px; }
        .info-row-val { color: var(--espresso); }

        /* ── Mini Map ── */
        #mini-map {
            height: 240px;
            border-radius: 16px;
            overflow: hidden;
            border: 3px solid var(--warm-cappuccino);
        }

        /* ── Rating Summary ── */
        .rating-summary {
            display: flex;
            align-items: center;
            gap: 24px;
            background: var(--bg-latte);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px;
        }
        .rating-big {
            font-family: 'Lora', serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--espresso);
            line-height: 1;
        }
        .rating-stars-big { display: flex; gap: 4px; }
        .rating-stars-big .star { font-size: 1.4rem; }
        .star-on  { color: #D4A017; }
        .star-off { color: #D6BFA6; }
        .rating-count { font-size: .85rem; color: var(--mocha); margin-top: 4px; }

        /* ── Ulasan List ── */
        .ulasan-list { display: flex; flex-direction: column; gap: 20px; }

        .ulasan-item {
            background: var(--white);
            border-radius: 18px;
            padding: 24px 28px;
            box-shadow: 0 4px 18px rgba(59,42,34,.07);
            border-left: 4px solid var(--caramel);
            transition: transform .2s;
            animation: fadeSlideIn .4s ease both;
        }
        .ulasan-item:hover { transform: translateX(4px); }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .ulasan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .ulasan-nama {
            font-weight: 700;
            color: var(--espresso);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ulasan-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--espresso);
            color: var(--bg-latte);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Lora', serif;
            font-weight: 700;
            font-size: .9rem;
            flex-shrink: 0;
        }
        .ulasan-rating { display: flex; gap: 2px; }
        .ulasan-rating .star { font-size: 1rem; }
        .ulasan-tgl {
            font-size: .75rem;
            color: var(--mocha);
            opacity: .65;
            margin-bottom: 10px;
        }
        .ulasan-isi {
            color: var(--mocha);
            font-size: .9rem;
            line-height: 1.7;
        }

        .no-ulasan {
            text-align: center;
            padding: 48px 20px;
            background: var(--white);
            border-radius: 18px;
            color: var(--mocha);
        }
        .no-ulasan .no-ulas-icon { font-size: 3rem; margin-bottom: 14px; display: block; }
        .no-ulasan p { opacity: .7; }

        /* ── Sticky Sidebar ── */
        .detail-sidebar { position: sticky; top: 90px; }

        /* ── Form Card ── */
        .form-card {
            background: var(--white);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 8px 32px rgba(59,42,34,.12);
            margin-bottom: 24px;
        }
        .form-title {
            font-family: 'Lora', serif;
            font-size: 1.25rem;
            color: var(--espresso);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--mocha);
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--warm-cappuccino);
            border-radius: 12px;
            background: var(--bg-latte);
            color: var(--espresso);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .9rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            resize: vertical;
        }
        .form-control:focus {
            border-color: var(--espresso);
            box-shadow: 0 0 0 3px rgba(59,42,34,.1);
            background: var(--white);
        }
        .form-control::placeholder { color: var(--warm-cappuccino); }

        /* Star picker */
        .star-picker { display: flex; gap: 8px; cursor: pointer; }
        .star-picker .sp-star {
            font-size: 2rem;
            color: var(--warm-cappuccino);
            transition: color .15s, transform .1s;
            user-select: none;
        }
        .star-picker .sp-star:hover,
        .star-picker .sp-star.selected { color: #D4A017; transform: scale(1.15); }

        /* Flash messages */
        .flash-msg {
            border-radius: 14px;
            padding: 16px 20px;
            font-size: .88rem;
            font-weight: 600;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: fadeSlideIn .35s ease;
        }
        .flash-success {
            background: #D1FAE5;
            color: #065F46;
            border-left: 4px solid #10B981;
        }
        .flash-error {
            background: #FEE2E2;
            color: #7F1D1D;
            border-left: 4px solid #EF4444;
        }

        /* ── Back Button ── */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-latte);
            color: var(--espresso);
            border: 2px solid var(--warm-cappuccino);
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: .85rem;
            cursor: pointer;
            text-decoration: none;
            transition: .2s;
            margin-bottom: 20px;
        }
        .btn-back:hover { background: var(--warm-cappuccino); }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--espresso);
            color: var(--bg-latte);
            border: none;
            border-radius: 30px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .1s;
            margin-top: 6px;
        }
        .btn-submit:hover { background: var(--mocha); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        /* ── Responsive ── */
        @media (max-width: 960px) {
            .detail-body { grid-template-columns: 1fr; }
            .detail-sidebar { position: static; }
            .detail-hero-title { font-size: 2rem; }
        }
        @media (max-width: 600px) {
            .detail-body { padding: 30px 5%; }
            .breadcrumb { padding: 12px 5%; }
            .detail-hero-overlay { padding: 32px 5%; }
        }
    </style>
</head>
<body>

<?php
// ── Helpers ──────────────────────────────────────────────
$kat      = strtolower(trim($tempat['kategori'] ?? 'kafe'));
$katIcons = ['kafe' => '☕', 'warkop' => '🍵', 'resto' => '🍽️', 'taman' => '🌳'];
$katIcon  = $katIcons[$kat] ?? '📍';
$katColors= ['kafe' => '#FF6B35', 'warkop' => '#4ECDC4', 'resto' => '#45B7D1', 'taman' => '#96CEB4'];
$katColor = $katColors[$kat] ?? '#B08968';

$allFac = [
    'wifi'         => ['📶', 'Free Wifi'],
    'ac'           => ['❄️',  'AC'],
    'colokan'      => ['🔌', 'Colokan'],
    'outdoor'      => ['🪑', 'Outdoor'],
    'musholla'     => ['🕌', 'Musholla'],
    'parkir_mobil' => ['🚗', 'Parkir Mobil'],
    'toilet'       => ['🚻', 'Toilet'],
];

function starHtml(float $r, string $onCls = 'star-on', string $offCls = 'star-off'): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="star ' . ($i <= round($r) ? $onCls : $offCls) . '">★</span>';
    }
    return $html;
}
?>

<!-- ── Navbar ── -->
<nav class="navbar">
    <div class="logo">Nongkrong Polmed</div>
    <ul class="nav-links">
        <li><a href="<?= base_url('/') ?>">Home</a></li>
        <li><a href="<?= base_url('/') ?>#dashboard">Map</a></li>
        <li><a href="<?= base_url('/') ?>#categories">Categories</a></li>
        <li><a href="<?= base_url('/') ?>#team">About</a></li>
        <li><a href="<?= base_url('/') ?>#footer">Contact</a></li>
    </ul>
</nav>

<!-- ── Breadcrumb ── -->
<div class="breadcrumb">
    <a href="<?= base_url('/') ?>">🏠 Home</a>
    <span>/</span>
    <span><?= htmlspecialchars($tempat['nama_tempat'] ?? 'Detail') ?></span>
</div>

<!-- ── Hero ── -->
<div class="detail-hero">
    <?php if (!empty($tempat['foto_url'])): ?>
        <img class="detail-hero-img" src="<?= htmlspecialchars($tempat['foto_url']) ?>" alt="<?= htmlspecialchars($tempat['nama_tempat'] ?? '') ?>">
    <?php else: ?>
        <div class="detail-hero-placeholder"><?= $katIcon ?></div>
    <?php endif; ?>

    <div class="detail-hero-overlay">
        <span class="detail-kat-badge"><?= $katIcon ?> <?= strtoupper($kat) ?></span>
        <h1 class="detail-hero-title"><?= htmlspecialchars($tempat['nama_tempat'] ?? '') ?></h1>
        <div class="detail-hero-meta">
            <span>⭐ <?= $tempat['rating'] ?? '-' ?> / 5</span>
            <span>📍 <?= htmlspecialchars($tempat['alamat'] ?? '-') ?></span>
            <span>🕐 <?= htmlspecialchars($tempat['jam_buka'] ?? '-') ?></span>
            <span>💰 Rp <?= number_format($tempat['harga_min'] ?? 0, 0, ',', '.') ?> – <?= number_format($tempat['harga_max'] ?? 0, 0, ',', '.') ?></span>
        </div>
    </div>
</div>

<!-- ── Body ── -->
<div class="detail-body">

    <!-- ══ KOLOM KIRI ══ -->
    <div class="detail-main">

        <?php if (!empty($successMsg)): ?>
        <div class="flash-msg flash-success">
            <span>✅</span>
            <span><?= htmlspecialchars($successMsg) ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
        <div class="flash-msg flash-error">
            <span>⚠️</span>
            <span><?= htmlspecialchars($errorMsg) ?></span>
        </div>
        <?php endif; ?>

        <!-- Info Tempat -->
        <div class="info-card">
            <div class="info-card-title">📋 Informasi Tempat</div>

            <div class="info-row">
                <span class="info-row-icon">📍</span>
                <div>
                    <div class="info-row-label">Alamat</div>
                    <div class="info-row-val"><?= htmlspecialchars($tempat['alamat'] ?? '-') ?></div>
                </div>
            </div>

            <div class="info-row">
                <span class="info-row-icon">🕐</span>
                <div>
                    <div class="info-row-label">Jam Buka</div>
                    <div class="info-row-val"><?= htmlspecialchars($tempat['jam_buka'] ?? '-') ?></div>
                </div>
            </div>

            <div class="info-row">
                <span class="info-row-icon">💰</span>
                <div>
                    <div class="info-row-label">Range Harga</div>
                    <div class="info-row-val">
                        Rp <?= number_format($tempat['harga_min'] ?? 0, 0, ',', '.') ?>
                        &ndash;
                        Rp <?= number_format($tempat['harga_max'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($tempat['kontak_ig_wa'])): ?>
            <div class="info-row">
                <span class="info-row-icon">📲</span>
                <div>
                    <div class="info-row-label">Kontak</div>
                    <div class="info-row-val"><?= htmlspecialchars($tempat['kontak_ig_wa']) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($tempat['catatan'])): ?>
            <div class="info-row">
                <span class="info-row-icon">📝</span>
                <div>
                    <div class="info-row-label">Catatan</div>
                    <div class="info-row-val"><?= htmlspecialchars($tempat['catatan']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Fasilitas -->
        <div class="info-card">
            <div class="info-card-title">🛎️ Fasilitas</div>
            <div class="fac-chips">
                <?php foreach ($allFac as $key => [$icon, $label]): ?>
                <div class="fac-chip <?= empty($tempat[$key]) ? 'off' : '' ?>">
                    <?= $icon ?> <?= $label ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Mini Map -->
        <?php if (!empty($tempat['latitude']) && !empty($tempat['longitude'])): ?>
        <div class="info-card">
            <div class="info-card-title">🗺️ Lokasi di Peta</div>
            <div id="mini-map"></div>
        </div>
        <?php endif; ?>

        <!-- ═══════ ULASAN ═══════ -->
        <div class="info-card">
            <div class="info-card-title">💬 Ulasan Pengunjung</div>

            <!-- Rating Summary -->
            <div class="rating-summary">
                <div>
                    <div class="rating-big"><?= $avgRating > 0 ? $avgRating : '-' ?></div>
                    <div class="rating-stars-big"><?= starHtml((float)$avgRating) ?></div>
                    <div class="rating-count"><?= count($ulasan) ?> ulasan</div>
                </div>
                <div style="flex:1; font-size:.85rem; color:var(--mocha); line-height:1.8;">
                    <?php
                    // Bar distribusi rating
                    $total = count($ulasan);
                    for ($i = 5; $i >= 1; $i--):
                        $cnt = count(array_filter($ulasan, fn($u) => (int)$u['rating'] === $i));
                        $pct = $total > 0 ? round($cnt / $total * 100) : 0;
                    ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="color:#D4A017;font-size:.85rem;">★ <?= $i ?></span>
                        <div style="flex:1;height:6px;background:#EFE3D0;border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct ?>%;background:#D4A017;border-radius:3px;transition:width .5s;"></div>
                        </div>
                        <span style="min-width:28px;text-align:right;"><?= $cnt ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Daftar Ulasan -->
            <?php if (empty($ulasan)): ?>
            <div class="no-ulasan">
                <span class="no-ulas-icon">💭</span>
                <h4 style="margin-bottom:8px;">Belum Ada Ulasan</h4>
                <p>Jadilah yang pertama memberikan ulasan<br>untuk tempat ini!</p>
            </div>
            <?php else: ?>
            <div class="ulasan-list">
                <?php foreach ($ulasan as $i => $u): ?>
                <div class="ulasan-item" style="animation-delay:<?= $i * 0.07 ?>s">
                    <div class="ulasan-header">
                        <div class="ulasan-nama">
                            <div class="ulasan-avatar"><?= strtoupper(substr($u['nama_pengunjung'] ?? 'A', 0, 1)) ?></div>
                            <?= htmlspecialchars($u['nama_pengunjung'] ?? 'Anonim') ?>
                        </div>
                        <div class="ulasan-rating"><?= starHtml((float)($u['rating'] ?? 0)) ?></div>
                    </div>
                    <div class="ulasan-tgl">
                        🕐 <?= date('d M Y, H:i', strtotime($u['created_at'] ?? 'now')) ?> WIB
                    </div>
                    <div class="ulasan-isi"><?= nl2br(htmlspecialchars($u['isi_ulasan'] ?? '')) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /detail-main -->

    <!-- ══ KOLOM KANAN (Sidebar) ══ -->
    <div class="detail-sidebar">

        <!-- ── Form Ulasan ── -->
        <div class="form-card">
            <div class="form-title">✍️ Tulis Ulasan</div>

            <form action="<?= base_url('/ulasan/simpan') ?>" method="POST" id="formUlasan">
                <?= csrf_field() ?>
                <input type="hidden" name="tempat_nongkrong_id" value="<?= (int)($tempat['id'] ?? 0) ?>">

                <div class="form-group">
                    <label class="form-label" for="nama_pengunjung">Nama Kamu</label>
                    <input
                        type="text"
                        id="nama_pengunjung"
                        name="nama_pengunjung"
                        class="form-control"
                        placeholder="Contoh: Budi Santoso"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <div class="star-picker" id="starPicker">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="sp-star" data-val="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="0">
                    <div id="ratingError" style="color:#EF4444;font-size:.78rem;margin-top:6px;display:none;">
                        Pilih rating terlebih dahulu.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="isi_ulasan">Isi Ulasan</label>
                    <textarea
                        id="isi_ulasan"
                        name="isi_ulasan"
                        class="form-control"
                        rows="5"
                        placeholder="Ceritakan pengalaman kamu di sini…"
                        maxlength="1000"
                        required
                    ></textarea>
                    <div style="text-align:right;font-size:.72rem;color:var(--mocha);opacity:.6;margin-top:4px;">
                        <span id="charCount">0</span> / 1000
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    🚀 Kirim Ulasan
                </button>
            </form>
        </div>

        <!-- ── Tombol Kembali ── -->
        <a href="<?= base_url('/') ?>" class="btn-back">← Kembali ke Peta</a>

    </div><!-- /detail-sidebar -->

</div><!-- /detail-body -->

<!-- ══ Leaflet ══ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function () {
    'use strict';

    /* ── Mini Map ─────────────────────────────────────────── */
    var lat = <?= (float)($tempat['latitude']  ?? 0) ?>;
    var lng = <?= (float)($tempat['longitude'] ?? 0) ?>;
    var nama = <?= json_encode($tempat['nama_tempat'] ?? '') ?>;
    var color = <?= json_encode($katColor) ?>;

    if (lat && lng && document.getElementById('mini-map')) {
        var miniMap = L.map('mini-map', { zoomControl: true, scrollWheelZoom: false })
            .setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(miniMap);

        L.circleMarker([lat, lng], {
            radius: 12, color: '#fff', fillColor: color,
            fillOpacity: 1, weight: 3
        })
        .bindPopup('<b>' + nama + '</b>')
        .addTo(miniMap)
        .openPopup();
    }

    /* ── Star Picker ──────────────────────────────────────── */
    var stars = document.querySelectorAll('.sp-star');
    var ratingInput = document.getElementById('ratingInput');

    stars.forEach(function (star) {
        star.addEventListener('mouseover', function () {
            var val = parseInt(this.dataset.val, 10);
            stars.forEach(function (s) {
                s.classList.toggle('selected', parseInt(s.dataset.val, 10) <= val);
            });
        });

        star.addEventListener('click', function () {
            var val = parseInt(this.dataset.val, 10);
            ratingInput.value = val;
            stars.forEach(function (s) {
                s.classList.toggle('selected', parseInt(s.dataset.val, 10) <= val);
            });
            document.getElementById('ratingError').style.display = 'none';
        });
    });

    document.getElementById('starPicker').addEventListener('mouseleave', function () {
        var current = parseInt(ratingInput.value, 10);
        stars.forEach(function (s) {
            s.classList.toggle('selected', parseInt(s.dataset.val, 10) <= current);
        });
    });

    /* ── Char counter ─────────────────────────────────────── */
    var isiUlasan = document.getElementById('isi_ulasan');
    var charCount = document.getElementById('charCount');
    if (isiUlasan && charCount) {
        isiUlasan.addEventListener('input', function () {
            charCount.textContent = this.value.length;
        });
    }

    /* ── Form validation ──────────────────────────────────── */
    document.getElementById('formUlasan').addEventListener('submit', function (e) {
        var rating = parseInt(ratingInput.value, 10);
        if (!rating || rating < 1) {
            e.preventDefault();
            document.getElementById('ratingError').style.display = 'block';
            document.getElementById('starPicker').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        var btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.textContent = '⏳ Menyimpan…';
    });

    /* ── Auto-scroll ke flash message ────────────────────── */
    var flash = document.querySelector('.flash-msg');
    if (flash) {
        setTimeout(function () {
            flash.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }
})();
</script>

</body>
</html>