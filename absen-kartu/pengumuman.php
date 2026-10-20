<?php
require_once 'config/database.php';
$judul_halaman = "Pengumuman - Absensi.";
include 'includes/header.php';

// --- PROSES TAMBAH PENGUMUMAN ---
if (isset($_POST['tambah_pengumuman'])) {
    $judul = $conn->real_escape_string($_POST['judul']);
    $isi = $conn->real_escape_string($_POST['isi']);
    
    $sql = "INSERT INTO pengumuman (judul, isi) VALUES ('$judul', '$isi')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>showModal('Berhasil!', 'Pengumuman berhasil ditambahkan.', 'success', function() { window.location='pengumuman.php'; });</script>";
    } else {
        echo "<script>showModal('Gagal!', 'Terjadi kesalahan: " . $conn->error . "', 'danger', function() { window.location='pengumuman.php'; });</script>";
    }
}

// --- PROSES HAPUS PENGUMUMAN ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM pengumuman WHERE id = $id");
    echo "<script>showModal('Berhasil!', 'Pengumuman berhasil dihapus!', 'success', function() { window.location='pengumuman.php'; });</script>";
}

$queryPengumuman = $conn->query("SELECT * FROM pengumuman ORDER BY id DESC");
?>

    <div class="header">
      <h1>Manajemen Pengumuman</h1>
      <div class="clock" id="clock">--:--:--</div>
    </div>

    <div class="panel">
      <h2>Buat Pengumuman Baru</h2>
      <form method="POST">
        <div class="form-group">
          <label>Judul Pengumuman</label>
          <input type="text" name="judul" placeholder="Contoh: Info Libur Semester" required>
        </div>
        <div class="form-group">
          <label>Isi Pengumuman</label>
          <textarea name="isi" rows="4" placeholder="Tulis detail pengumuman..." required></textarea>
        </div>
        <button type="submit" name="tambah_pengumuman" class="btn">Kirim Pengumuman</button>
      </form>
    </div>

    <div class="panel">
      <h2>Daftar Pengumuman</h2>
      <?php if ($queryPengumuman->num_rows > 0): ?>
        <?php while($row = $queryPengumuman->fetch_assoc()): ?>
          <div class="card-announce">
            <h3><?= htmlspecialchars($row['judul']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['isi'])) ?></p>
            <a href="#" class="btn btn-danger" onclick="showModal('Hapus Pengumuman', 'Apakah Anda yakin ingin menghapus pengumuman ini?', 'danger', function(ok) { if(ok) window.location='pengumuman.php?hapus=<?= $row['id'] ?>'; })">Hapus</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="color: #6B7280;">Belum ada pengumuman.</p>
      <?php endif; ?>
    </div>

<?php include 'includes/footer.php'; ?>