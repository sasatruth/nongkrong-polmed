<section class="team-section" id="team">
    <div class="text-center">
        <h2 class="section-title">Tim Pengembang</h2>
        <p class="section-subtitle">Mata Kuliah: Sistem Informasi Geografis (Kelompok 6)</p>
    </div>
    <div class="team-grid">
        <?php foreach ($anggota as $ag): ?>
        <div class="member-card">
            <div class="member-photo">
                <?php if (!empty($ag['foto'])): ?>
                    <img src="<?= base_url('img/' . $ag['foto']) ?>" alt="<?= $ag['nama'] ?>">
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#F3E9D7;font-size:1.6rem;font-family:'Lora',serif;font-weight:700;">
                        <?= strtoupper(substr($ag['nama'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <h4><?= $ag['nama'] ?></h4>
            <p><?= $ag['peran'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<footer class="footer">
    <div class="footer-grid" id="footer">
        <div class="footer-item">
            <h4>Nongkrong Polmed</h4>
            <p>Platform pemetaan tempat nongkrong mahasiswa berbasis Sistem Informasi Geografis</p>
            <div class="footer-tag">Sistem Informasi Geografis</div>
        </div>
        <div class="footer-item">
            <h4>Lokasi Cakupan</h4>
            <p>Area Politeknik Negeri Medan<br>Jl. Almamater No. 1 Medan</p>
        </div>
        <div class="footer-item">
            <h4>Jam Operasi</h4>
            <p>Bervariasi per Lokasi:<br>09:00 – 23:00 atau 24 jam</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Nongkrong Polmed — Geographic Information System Project</p>
        <p>Developed with ❤ for Polmed Students</p>
    </div>
</footer>

<!-- ══ Leaflet core (CSS di header.php sudah ada) ══ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- ══ App script ══ -->
<script src="<?= base_url('assets/js/script.js') ?>"></script>

</body>
</html>