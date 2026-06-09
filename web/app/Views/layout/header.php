<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nongkrong Polmed | GIS Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

    <nav class="navbar">
        <div class="logo">Nongkrong Polmed</div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#dashboard">Map</a></li>
            <li><a href="#categories">Categories</a></li>
            <li><a href="#team">About</a></li>
            <li><a href="#footer">Contacts</a></li>
        </ul>
        <div class="search-container" style="position: relative;">
            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:0.6; color:#fff; pointer-events:none;">🔍</span>
            <input type="text" class="search-input" placeholder="Cari nama tempat..." id="searchInput" oninput="liveSearch()" style="padding-left: 35px;">
        </div>
    </nav>

    <header class="hero">
        <h1>Pemetaan Digital<br>Tempat Nongkrong<br>Mahasiswa Polmed</h1>
        <p>Eksplorasi ruang santai berdasarkan fasilitas dan harga melalui visualisasi data spasial</p>
        <a href="#dashboard" class="btn-hero">Lihat Peta</a>
    </header>