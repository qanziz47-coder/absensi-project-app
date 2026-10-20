<?php
require_once 'config/database.php';
$judul_halaman = "Dashboard - Absensi.";
include 'includes/header.php';
require_once 'config/helpers.php';

// --- PROSES TAMBAH USER ---
if (isset($_POST['tambah_user'])) {
    $uid = $conn->real_escape_string($_POST['uid']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    
    $sql = "INSERT INTO users (uid, nama, kelas) VALUES ('$uid', '$nama', '$kelas')";
    if ($conn->query($sql) === TRUE) {
        catatLog($conn, "Menambahkan user baru: $nama");
        echo "<script>showModal('Berhasil!', 'User berhasil ditambahkan.', 'success', function() { window.location='dashboard.php'; });</script>";
    } else {
        echo "<script>showModal('Gagal!', 'Terjadi kesalahan: " . $conn->error . "', 'danger', function() { window.location='dashboard.php'; });</script>";
    }
}

// --- PROSES HAPUS USER ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $queryNama = $conn->query("SELECT nama FROM users WHERE id = $id");
    $dataNama = $queryNama->fetch_assoc();
    $namaDihapus = $dataNama ? $dataNama['nama'] : 'Tidak diketahui';
    
    $conn->query("DELETE FROM users WHERE id = $id");
    catatLog($conn, "Menghapus user: $namaDihapus");
    echo "<script>showModal('Berhasil!', 'User berhasil dihapus!', 'success', function() { window.location='dashboard.php'; });</script>";
}

// --- PROSES HAPUS RIWAYAT ABSEN ---
if (isset($_GET['hapus_absen'])) {
    $id_absen = $_GET['hapus_absen'];
    $conn->query("DELETE FROM absensi WHERE id = $id_absen");
    catatLog($conn, "Menghapus riwayat absensi ID: $id_absen");
    echo "<script>showModal('Berhasil!', 'Data absensi berhasil dihapus!', 'success', function() { window.location='dashboard.php'; });</script>";
}

// Ambil data
$hariIni = date('Y-m-d');
$queryTotal = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE tanggal = '$hariIni'");
$totalHariIni = $queryTotal->fetch_assoc()['total'];
$queryRiwayat = $conn->query("SELECT a.*, u.foto FROM absensi a LEFT JOIN users u ON a.uid = u.uid ORDER BY a.id DESC LIMIT 10");
$queryUsers = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

    <div class="header">
      <h1>Ringkasan Hari Ini</h1>
      <div style="display: flex; gap: 15px; align-items: center;">
        <a href="export.php" class="btn" style="background: #10B981; padding: 10px 18px; font-size: 13px;">
          Download Laporan (Excel)
        </a>
        <div class="clock" id="clock">--:--:--</div>
      </div>
    </div>

    <div class="cards">
      <div class="card card-blue">
        <h3>Total Absen</h3>
        <div class="value"><?= $totalHariIni ?></div>
      </div>
      <div class="card card-purple">
        <h3>Total User</h3>
        <div class="value"><?= $queryUsers->num_rows ?></div>
      </div>
    </div>

    <!-- KOTAK QR CODE -->
    <div class="panel" style="text-align: center;">
      <h2>Scan QR Code untuk Absen</h2>
      <div class="qr-container">
        <div id="qrcode" style="background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);"></div>
        <p style="margin-top: 15px; font-size: 14px; color: var(--text-muted);">
          Kode: <span id="kodeHariIni" style="font-weight: 700; color: var(--primary);">Memuat...</span>
        </p>
      </div>
    </div>

    <!-- Form Tambah User -->
    <div class="panel">
      <h2>Tambah Pengguna</h2>
      <form method="POST" action="">
        <div class="form-group">
          <input type="text" name="uid" placeholder="UID Kartu" required>
          <input type="text" name="nama" placeholder="Nama Lengkap" required>
          <input type="text" name="kelas" placeholder="Kelas">
          <button type="submit" name="tambah_user" class="btn">Tambah</button>
        </div>
      </form>
    </div>

    <!-- Daftar User -->
    <div class="panel">
      <h2>Daftar Pengguna</h2>
      <table>
        <thead>
          <tr>
            <th>Nama</th>
            <th>UID Kartu</th>
            <th>Kelas</th>
            <th>Status</th>
            <th style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($queryUsers->num_rows > 0): ?>
            <?php while($row = $queryUsers->fetch_assoc()): ?>
              <tr>
                <td style="font-weight: 600;"><?= htmlspecialchars($row['nama']) ?></td>
                <td style="color: var(--text-muted);"><?= htmlspecialchars($row['uid']) ?></td>
                <td><?= htmlspecialchars($row['kelas']) ?></td>
                <td><?= $row['status'] ?></td>
                <td style="text-align: right;">
                  <!-- Tag <a> ditutup dengan benar -->
                  <a href="#" class="btn btn-danger" onclick="showModal('Hapus User', 'Apakah Anda yakin ingin menghapus user ini?', 'danger', function(ok) { if(ok) window.location='dashboard.php?hapus=<?= $row['id'] ?>'; })">Hapus</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Belum ada pengguna.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Riwayat Absensi (Dibungkus div id="tabel-riwayat" untuk auto-refresh) -->
    <div class="panel">
      <h2>Aktivitas Terbaru</h2>
      <div id="tabel-riwayat">
        <table>
          <thead>
            <tr>
              <th>Pengguna</th>
              <th>Waktu</th>
              <th>Status</th>
              <th style="text-align: right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($queryRiwayat->num_rows > 0): ?>
              <?php while($row = $queryRiwayat->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <?php $foto_url = !empty($row['foto']) ? $row['foto'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png'; ?>
                      <img src="<?= htmlspecialchars($foto_url) ?>" class="foto-tabel" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png';">
                      <div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($row['nama']) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($row['uid']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div style="font-weight: 600;"><?= $row['jam'] ?></div>
                    <div style="font-size: 12px; color: var(--text-muted);"><?= $row['tanggal'] ?></div>
                  </td>
                  <td><span class="badge badge-<?= $row['status'] ?>"><?= strtoupper($row['status']) ?></span></td>
                  <td style="text-align: right;">
                    <a href="#" class="btn btn-danger" onclick="showModal('Hapus Riwayat', 'Apakah Anda yakin ingin menghapus data absensi ini?', 'danger', function(ok) { if(ok) window.location='dashboard.php?hapus_absen=<?= $row['id'] ?>'; })">Hapus</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" style="text-align:center; color: var(--text-muted);">Belum ada aktivitas.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  <script>
    // QR Code
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const kodeAbsen = `ABSEN_SEKOLAH_${yyyy}-${mm}-${dd}`;
    document.getElementById('kodeHariIni').innerText = kodeAbsen;
    new QRCode(document.getElementById("qrcode"), {
        text: kodeAbsen, width: 180, height: 180, colorDark: "#1E1B4B", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.H
    });

    // Suara Panggilan
    let lastId = 0;
    function cekSuaraAbsen() {
        fetch('api/latest.php?t=' + Date.now())
            .then(res => res.json())
            .then(data => {
                if (data.ada && data.id > lastId) {
                    lastId = data.id;
                    panggilSuara(data);
                }
            }).catch(err => console.log('Error:', err));
    }
    function panggilSuara(data) {
        if ('speechSynthesis' in window) {
            let jamSingkat = data.jam.substring(0, 5);
            let teks = `${data.nama}, Absen ${data.status}, pukul ${jamSingkat}`;
            let speech = new SpeechSynthesisUtterance(teks);
            speech.lang = 'id-ID'; speech.rate = 0.85; speech.pitch = 1.1; 
            window.speechSynthesis.speak(speech);
        }
    }
    setInterval(cekSuaraAbsen, 3000);

    // Auto Refresh Tabel Riwayat Tanpa Reload
    function refreshTabel() {
        fetch('api/dashboard_data.php')
            .then(res => res.text())
            .then(html => {
                document.getElementById('tabel-riwayat').innerHTML = html;
            }).catch(err => console.log('Gagal refresh:', err));
    }
    setInterval(refreshTabel, 5000);
  </script>

<?php include 'includes/footer.php'; ?>