<?php
require_once 'config/database.php';
$judul_halaman = "Tugas Sekolah - Absensi.";
include 'includes/header.php';

// --- PROSES TAMBAH TUGAS ---
if (isset($_POST['tambah_tugas'])) {
    $judul = $conn->real_escape_string($_POST['judul']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $mapel = $conn->real_escape_string($_POST['mapel']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    $deadline = $conn->real_escape_string($_POST['deadline']);
    
    $sql = "INSERT INTO tugas (judul, deskripsi, mata_pelajaran, target_kelas, deadline) 
            VALUES ('$judul', '$deskripsi', '$mapel', '$kelas', '$deadline')";
            
    if ($conn->query($sql) === TRUE) {
        echo "<script>showModal('Berhasil!', 'Tugas berhasil ditambahkan.', 'success', function() { window.location='tugas.php'; });</script>";
    } else {
        echo "<script>showModal('Gagal!', 'Terjadi kesalahan: " . $conn->error . "', 'danger', function() { window.location='tugas.php'; });</script>";
    }
}

// --- PROSES HAPUS TUGAS ---
if (isset($_GET['hapus_tugas'])) {
    $id = $_GET['hapus_tugas'];
    $conn->query("DELETE FROM tugas WHERE id = $id");
    echo "<script>showModal('Berhasil!', 'Tugas berhasil dihapus!', 'success', function() { window.location='tugas.php'; });</script>";
}

$queryTugas = $conn->query("SELECT * FROM tugas ORDER BY id DESC");
?>

    <div class="header">
      <h1>Manajemen Tugas</h1>
      <div class="clock" id="clock">--:--:--</div>
    </div>

    <!-- Form Tambah Tugas -->
    <div class="panel">
      <h2>Buat Tugas Baru</h2>
      <form method="POST" action="">
        <div class="form-grid">
            <div class="form-group">
              <label>Judul Tugas</label>
              <input type="text" name="judul" placeholder="Contoh: Latihan Soal Matematika" required>
            </div>
            <div class="form-group">
              <label>Mata Pelajaran</label>
              <input type="text" name="mapel" placeholder="Contoh: Matematika" required>
            </div>
            <div class="form-group">
              <label>Target Kelas</label>
              <input type="text" name="kelas" placeholder="Contoh: XI RPL 1" required>
            </div>
            <div class="form-group">
              <label>Deadline</label>
              <input type="date" name="deadline" required>
            </div>
            <div class="form-group full-width">
              <label>Deskripsi Tugas</label>
              <textarea name="deskripsi" rows="4" placeholder="Tulis detail tugas di sini..." required></textarea>
            </div>
        </div>
        <button type="submit" name="tambah_tugas" class="btn">Buat Tugas</button>
      </form>
    </div>

    <!-- Daftar Tugas -->
    <div class="panel">
      <h2>Daftar Tugas Aktif</h2>
      <table>
        <thead>
          <tr>
            <th style="width: 40%;">Informasi Tugas</th>
            <th>Target & Deadline</th>
            <th>Deskripsi</th>
            <th style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($queryTugas->num_rows > 0): ?>
            <?php while($row = $queryTugas->fetch_assoc()): ?>
              <tr>
                <td>
                  <div style="font-size: 16px; font-weight: 700; color: #1E1B4B; margin-bottom: 8px;"><?= htmlspecialchars($row['judul']) ?></div>
                  <span class="badge-mapel"><?= htmlspecialchars($row['mata_pelajaran']) ?></span>
                </td>
                <td>
                  <div style="margin-bottom: 8px;"><span class="badge-kelas">Kelas: <?= htmlspecialchars($row['target_kelas']) ?></span></div>
                  <div class="deadline">⏰ <?= date('d M Y', strtotime($row['deadline'])) ?></div>
                </td>
                <td style="color: var(--text-muted); font-size: 13px; line-height: 1.6;">
                  <?= nl2br(htmlspecialchars($row['deskripsi'])) ?>
                </td>
                <td style="text-align: right;">
                  <a href="#" class="btn btn-danger" onclick="showModal('Hapus Tugas', 'Apakah Anda yakin ingin menghapus tugas ini?', 'danger', function(ok) { if(ok) window.location='tugas.php?hapus_tugas=<?= $row['id'] ?>'; })">Hapus</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center; color: var(--text-muted); padding: 40px;">Belum ada tugas yang dibuat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php include 'includes/footer.php'; ?>