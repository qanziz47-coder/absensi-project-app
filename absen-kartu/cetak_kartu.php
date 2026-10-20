<?php
require_once 'config/database.php';
$judul_halaman = "Cetak Kartu Pelajar - Absensi.";
include 'includes/header.php';

$queryUsers = $conn->query("SELECT * FROM users ORDER BY nama ASC");
?>

    <div class="header">
      <h1>Cetak Kartu Pelajar</h1>
      <div class="clock" id="clock">--:--:--</div>
    </div>

    <!-- KONTEN CETAK KARTU -->
    <div style="display: flex; gap: 20px; flex-wrap: wrap; width: 100%;">
        
        <!-- KIRI: DAFTAR SISWA -->
        <div class="panel" style="flex: 1; min-width: 300px; max-height: 80vh; overflow-y: auto;">
            <h2>Pilih Siswa</h2>
            <?php while($row = $queryUsers->fetch_assoc()): 
                $foto = !empty($row['foto']) ? $row['foto'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png';
            ?>
                <div class="siswa-item" onclick="pilihSiswa(this, '<?= htmlspecialchars($row['nama']) ?>', '<?= htmlspecialchars($row['kelas']) ?>', '<?= htmlspecialchars($row['uid']) ?>', '<?= htmlspecialchars($foto) ?>')">
                    <img src="<?= htmlspecialchars($foto) ?>" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png';">
                    <div>
                        <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                        <small style="color:#6B7280;"><?= htmlspecialchars($row['kelas']) ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- KANAN: PREVIEW KARTU -->
        <div class="panel" style="width: 500px; max-width: 100%; display: flex; flex-direction: column; align-items: center;">
            <h2 style="margin-bottom:15px; color:#6B7280; font-size:14px; letter-spacing:1px; text-transform:uppercase;">Pratinjau Kartu</h2>
            
            <div class="kartu-wrapper" id="kartuDownload">
                <div class="kartu-header"></div>
                <div class="kartu-title">📖 KARTU PELAJAR</div>
                <img id="preview-foto" class="kartu-foto" src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png" crossorigin="anonymous">
                
                <div class="kartu-info">
                    <div class="kartu-nama" id="preview-nama">Pilih Siswa</div>
                    <div class="kartu-garis"></div>
                    <div class="kartu-kelas" id="preview-kelas">-</div>
                </div>

                <div class="kartu-motto">
                    <div class="bar"></div>
                    <div class="text">BELAJAR HARI INI<br>UNTUK MASA DEPAN</div>
                </div>

                <div class="kartu-qr" id="preview-qr"></div>
            </div>

            <button class="btn-print" id="btnDownload" style="width: 100%; padding: 15px; background: #4a45cf; color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 16px; cursor: pointer;">
                Download Kartu (PNG)
            </button>
        </div>
    </div>

<!-- CSS KHUSUS CETAK KARTU (Diletakkan di dalam file ini agar tidak mengganggu halaman lain) -->
<style>
    .siswa-item { display: flex; align-items: center; gap: 15px; padding: 12px; border-bottom: 1px solid #E5E7EB; cursor: pointer; transition: 0.2s; border-radius: 8px; }
    .siswa-item:hover, .siswa-item.active { background: #EEF2FF; }
    .siswa-item img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }

    .kartu-wrapper {
        width: 460px; max-width: 100%; height: 260px;
        position: relative; border-radius: 20px; overflow: hidden;
        background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        margin-bottom: 20px;
    }
    .kartu-header {
        position: absolute; top: 0; left: 0; width: 100%; height: 100px;
        background: linear-gradient(120deg, #25235a 0%, #3a36a8 60%, #4a45cf 100%);
        clip-path: polygon(0 0, 100% 0, 100% 70%, 85% 70%, 75% 100%, 0 100%);
    }
    .kartu-header::after {
        content: ""; position: absolute; top: 0; left: 70%; width: 30%; height: 100%;
        background: rgba(255,255,255,0.1); clip-path: polygon(50% 0, 100% 0, 100% 100%, 0 100%);
    }
    .kartu-title { position: absolute; top: 25px; right: 30px; color: #FFF; font-size: 14px; font-weight: 500; letter-spacing: 3px; display: flex; align-items: center; gap: 10px; }
    .kartu-foto { position: absolute; left: 25px; top: 45px; width: 140px; height: 140px; border-radius: 50%; border: 4px solid #4a45cf; background: #EEF0FB; box-shadow: 0 5px 15px rgba(0,0,0,0.1); object-fit: cover; z-index: 2; }
    .kartu-info { position: absolute; left: 185px; top: 120px; }
    .kartu-nama { font-size: 28px; font-weight: 800; color: #1E1B4B; text-transform: capitalize; }
    .kartu-garis { width: 40px; height: 4px; background: #4a45cf; border-radius: 2px; margin: 8px 0; }
    .kartu-kelas { font-size: 16px; font-weight: 500; color: #6b6f8f; letter-spacing: 3px; text-transform: uppercase; }
    .kartu-motto { position: absolute; left: 30px; bottom: 25px; display: flex; align-items: center; gap: 10px; }
    .kartu-motto .bar { width: 4px; height: 40px; background: #4a45cf; }
    .kartu-motto .text { font-size: 10px; font-weight: 500; color: #6b6f8f; letter-spacing: 3px; line-height: 1.6; }
    .kartu-qr { position: absolute; right: 30px; bottom: 25px; width: 100px; height: 100px; display: flex; justify-content: center; align-items: center; }
    .kartu-qr::before, .kartu-qr::after { content: ""; position: absolute; width: 20px; height: 20px; border: 3px solid #4a45cf; border-radius: 4px; }
    .kartu-qr::before { top: -5px; left: -5px; border-right: none; border-bottom: none; }
    .kartu-qr::after { bottom: -5px; right: -5px; border-left: none; border-top: none; }
</style>

<script>
    function pilihSiswa(el, nama, kelas, uid, foto) {
        document.querySelectorAll('.siswa-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        document.getElementById('preview-nama').innerText = nama;
        document.getElementById('preview-kelas').innerText = kelas;
        document.getElementById('preview-foto').src = foto;
        
        const qrPreview = document.getElementById('preview-qr');
        qrPreview.innerHTML = "";
        new QRCode(qrPreview, { text: uid, width: 90, height: 90, colorDark: "#1E1B4B", colorLight: "#ffffff" });
    }

    document.getElementById('btnDownload').addEventListener('click', function() {
        if(document.getElementById('preview-nama').innerText === 'Pilih Siswa') {
            showModal('Perhatian', 'Silakan pilih siswa terlebih dahulu!', 'danger');
            return;
        }

        const kartu = document.getElementById('kartuDownload');
        const btn = this;
        
        btn.innerHTML = "⏳ Memproses...";
        btn.disabled = true;

        html2canvas(kartu, { scale: 3, useCORS: true, backgroundColor: "#ffffff" }).then(canvas => {
            const link = document.createElement('a');
            const namaSiswa = document.getElementById('preview-nama').innerText.replace(/\s+/g, '_');
            link.download = 'Kartu_Pelajar_' + namaSiswa + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            btn.innerHTML = "Download Kartu (PNG)";
            btn.disabled = false;
        }).catch(err => {
            showModal('Gagal', 'Gagal membuat gambar. Pastikan koneksi internet stabil.', 'danger');
            console.error(err);
            btn.innerHTML = "Download Kartu (PNG)";
            btn.disabled = false;
        });
    });
</script>

<?php include 'includes/footer.php'; ?>