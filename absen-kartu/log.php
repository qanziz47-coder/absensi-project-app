<?php
require_once 'config/database.php';
$judul_halaman = "Log Aktivitas - Absensi.";
include 'includes/header.php';

// --- PROSES RESET LOG DENGAN PASSWORD ---
if (isset($_POST['reset_log'])) {
    $password_input = md5($_POST['password']);
    
    // Ambil password asli dari database
    $queryPass = $conn->query("SELECT nilai FROM pengaturan WHERE nama_pengaturan = 'password_reset_log'");
    $dataPass = $queryPass->fetch_assoc();
    
    if ($dataPass && $password_input === $dataPass['nilai']) {
        $conn->query("TRUNCATE TABLE log_aktivitas");
        echo "<script>showModal('Berhasil!', 'Semua log aktivitas berhasil dihapus.', 'success', function() { window.location='log.php'; });</script>";
    } else {
        echo "<script>showModal('Gagal!', 'Password salah! Log tidak dihapus.', 'danger', function() { window.location='log.php'; });</script>";
    }
}

$queryLog = $conn->query("SELECT * FROM log_aktivitas ORDER BY id DESC LIMIT 50");
?>

    <div class="header">
      <h1>Log Aktivitas Admin</h1>
      <div class="clock" id="clock">--:--:--</div>
    </div>

    <div class="panel">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; border: none;">50 Aktivitas Terakhir</h2>
        <!-- Tombol Reset dengan Pop-up Form -->
        <button class="btn btn-danger" onclick="tampilkanFormReset()">Reset Log</button>
      </div>
      
      <table>
        <thead>
          <tr>
            <th>Waktu</th>
            <th>Admin</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($queryLog->num_rows > 0): ?>
            <?php while($row = $queryLog->fetch_assoc()): ?>
              <tr>
                <td style="color: var(--text-muted); font-size: 13px;">
                  <?= date('d/m/Y H:i', strtotime($row['waktu'])) ?>
                </td>
                <td style="font-weight: 600;"><?= htmlspecialchars($row['nama_admin']) ?></td>
                <td><?= htmlspecialchars($row['aksi']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">Belum ada aktivitas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- FORM RESET TERSEMBUNYI -->
    <div id="formReset" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
      <div style="background: #FFF; padding: 30px; border-radius: 20px; max-width: 400px; width: 90%; text-align: center;">
        <div style="font-size: 50px; margin-bottom: 15px;">🔒</div>
        <h3 style="margin-bottom: 10px; color: #1E1B4B;">Reset Log Aktivitas</h3>
        <p style="color: #6B7280; font-size: 14px; margin-bottom: 20px;">Masukkan password khusus untuk menghapus semua log.</p>
        
        <form method="POST" action="">
          <input type="password" name="password" placeholder="Password Reset" required style="width: 100%; padding: 12px; border: 1px solid #E5E7EB; border-radius: 10px; margin-bottom: 15px; text-align: center;">
          
          <div style="display: flex; gap: 10px;">
            <button type="button" onclick="tutupFormReset()" style="flex: 1; padding: 12px; background: #F3F4F6; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">Batal</button>
            <button type="submit" name="reset_log" style="flex: 1; padding: 12px; background: #EF4444; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">Hapus Log</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function tampilkanFormReset() {
        document.getElementById('formReset').style.display = 'flex';
      }
      function tutupFormReset() {
        document.getElementById('formReset').style.display = 'none';
      }
    </script>

<?php include 'includes/footer.php'; ?>