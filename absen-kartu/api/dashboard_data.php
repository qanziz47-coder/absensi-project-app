<?php
require_once '../config/database.php';
$queryRiwayat = $conn->query("SELECT a.*, u.foto FROM absensi a LEFT JOIN users u ON a.uid = u.uid ORDER BY a.id DESC LIMIT 10");
?>
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
            <a href="dashboard.php?hapus_absen=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus riwayat ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="4" style="text-align:center; color: var(--text-muted);">Belum ada aktivitas.</td></tr>
    <?php endif; ?>
  </tbody>
</table>