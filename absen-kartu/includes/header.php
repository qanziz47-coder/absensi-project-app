<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Proteksi Login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../config/database.php';

// 3. Ambil Data Admin
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $queryAdmin = $conn->query("SELECT * FROM admin WHERE id = $admin_id");
    
    if ($queryAdmin && $queryAdmin->num_rows > 0) {
        $dataAdmin = $queryAdmin->fetch_assoc();
    } else {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} else {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <title><?= isset($judul_halaman) ? $judul_halaman : 'Absen.'; ?></title>
  <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
  <!-- Library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
      --primary: #4F46E5;       
      --bg-main: #F3F4F6;       
      --bg-card: #FFFFFF;       
      --text-main: #111827;     
      --text-muted: #6B7280;    
      --border: #E5E7EB;        
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
    body { background: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }

    /* --- SIDEBAR --- */
    .sidebar {
      width: 260px; background: #1E1B4B; display: flex; flex-direction: column;
      padding: 30px 20px; position: fixed; height: 100vh; box-shadow: 4px 0 15px rgba(0,0,0,0.05);
    }
    .sidebar h2 { font-size: 22px; font-weight: 700; color: #FFFFFF; margin-bottom: 40px; text-align: center; }
    .sidebar a {
      color: #A5B4FC; text-decoration: none; padding: 12px 15px; border-radius: 8px;
      margin-bottom: 5px; font-weight: 500; font-size: 14px; display: flex; align-items: center; transition: 0.2s;
    }
    .sidebar a:hover { background: #312E81; color: #FFFFFF; }
    .sidebar a.active { background: #4F46E5; color: #FFFFFF; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); }

    /* --- MAIN CONTENT --- */
    .main-content { margin-left: 260px; flex: 1; padding: 40px; width: calc(100% - 260px); }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .header h1 { font-size: 26px; font-weight: 700; color: #1E1B4B; }
    .clock { font-size: 14px; font-weight: 600; color: #4F46E5; background: #E0E7FF; padding: 10px 20px; border-radius: 30px; }

    /* --- STATISTIK --- */
    .cards { display: flex; gap: 20px; margin-bottom: 30px; }
    .card { padding: 25px; border-radius: 16px; flex: 1; }
    .card-blue { background: #DBEAFE; color: #1E3A8A; }
    .card-purple { background: #F3E8FF; color: #4C1D95; }
    .card h3 { font-size: 14px; font-weight: 600; margin-bottom: 10px; opacity: 0.8; }
    .card .value { font-size: 40px; font-weight: 800; }

    /* --- PANELS --- */
    .panel { background: var(--bg-card); border-radius: 16px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .panel h2 { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #1E1B4B; }

    /* --- TABEL --- */
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 16px 12px; text-align: left; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle; }
    th { color: var(--text-muted); font-weight: 600; font-size: 12px; text-transform: uppercase; }
    tr:hover { background: #F9FAFB; }
    .foto-tabel { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E5E7EB; }
    .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; }
    .badge-masuk { background: #D1FAE5; color: #065F46; }
    .badge-pulang { background: #FEF3C7; color: #92400E; }

    /* --- FORM & BUTTON --- */
    .form-group { display: flex; gap: 12px; margin-bottom: 20px; }
    .form-group input { flex: 1; padding: 12px 16px; border: 1px solid var(--border); border-radius: 10px; background: #F9FAFB; }
    .form-group input:focus { outline: none; border-color: var(--primary); background: #FFF; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .btn { padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block;}
    .btn:hover { background: #4338CA; }
    .btn-danger { background: #FEE2E2; color: #991B1B; font-size: 12px; padding: 8px 14px; font-weight: 700; }
    .btn-danger:hover { background: #FCA5A5; }
    
    .qr-container { display: flex; flex-direction: column; align-items: center; justify-content: center; background: #F8FAFC; padding: 20px; border-radius: 16px; border: 1px dashed #CBD5E1; }

    /* --- OVERLAY LOADING --- */
    #loading-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background-color: #4F46E5; display: flex; justify-content: center; align-items: center;
      z-index: 99999; transition: opacity 0.5s ease, visibility 0.5s ease;
      flex-direction:column;
    }
    .loader {
      width: 60px; height: 60px; border: 6px solid rgba(255, 255, 255, 0.3);
      border-top: 6px solid #FFFFFF; border-radius: 50%; animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    #loading-overlay.hidden { opacity: 0; visibility: hidden; }

    /* --- RESPONSIVE (Mobile) --- */
    @media (max-width: 768px) {
      .sidebar { width: 100%; height: auto; position: relative; padding: 15px; }
      .sidebar h2 { margin-bottom: 15px; }
      .main-content { margin-left: 0; width: 100%; padding: 20px; }
      .cards { flex-direction: column; }
      .form-group { flex-direction: column; }
    }
        /* --- PROFIL ADMIN DI SIDEBAR --- */
    /* --- PROFIL ADMIN DI SIDEBAR --- */
    .admin-profile {
      display: flex; align-items: center; gap: 12px;
      background: #312E81; padding: 12px; border-radius: 12px;
      margin-bottom: 25px; border: 1px solid #4338CA;
    }
    .admin-avatar-img {
      width: 45px; height: 45px; border-radius: 50%; object-fit: cover;
      border: 2px solid #A5B4FC; background: #FFF;
    }
    .admin-info { display: flex; flex-direction: column; }
    .admin-name { font-size: 14px; font-weight: 700; color: #FFFFFF; }
    .admin-role { font-size: 11px; color: #A5B4FC; }
    .lo{ font-size: 30px; font-weight: 700; color: #FFFFFF; margin-bottom:15px; }

        /* --- RESPONSIVE DESIGN (Mobile & Tablet) --- */
    @media (max-width: 768px) {
      body { flex-direction: column; }
      .sidebar { 
        width: 100%; height: auto; position: relative; padding: 15px; 
        flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 5px;
      }
      .sidebar h2 { width: 100%; margin-bottom: 15px; }
      .sidebar a { margin-bottom: 0; padding: 8px 12px; font-size: 12px; }
      .admin-profile { margin-bottom: 10px; width: 100%; justify-content: center; }
      
      /* Main Content */
      .main-content { margin-left: 0; width: 100%; padding: 15px; }
      .header { flex-direction: column; align-items: flex-start; gap: 10px; }
      .header h1 { font-size: 20px; }
      .cards { flex-direction: column; }
      
      /* Tabel bisa di-scroll ke samping */
      .panel { overflow-x: auto; }
      table { min-width: 600px; } /* Biarkan tabel tetap lebar, tapi bisa di-scroll */
    }

        /* --- POP-UP  --- */
    .modal-overlay {
      display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5); z-index: 9999;
      justify-content: center; align-items: center;
      opacity: 0; transition: opacity 0.3s ease;
    }
    .modal-overlay.show { display: flex; opacity: 1; }
    
    .modal-box {
      background: #FFF; padding: 30px; border-radius: 20px;
      max-width: 400px; width: 90%; text-align: center;
      transform: translateY(20px); transition: transform 0.3s ease;
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }
    .modal-overlay.show .modal-box { transform: translateY(0); }
    
    .modal-icon { font-size: 50px; margin-bottom: 15px; }
    .modal-box h3 { font-size: 20px; color: #1E1B4B; margin-bottom: 10px; }
    .modal-box p { color: #6B7280; font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
    
    .modal-buttons { display: flex; gap: 10px; justify-content: center; }
    .btn-cancel {
      padding: 10px 20px; border: 1px solid #E5E7EB; background: #FFF;
      color: #6B7280; border-radius: 10px; cursor: pointer; font-weight: 600; flex: 1;
    }
    .btn-cancel:hover { background: #F3F4F6; }
    .btn-confirm {
      padding: 10px 20px; border: none; background: #EF4444;
      color: #FFF; border-radius: 10px; cursor: pointer; font-weight: 600; flex: 1;
    }
    .btn-confirm:hover { background: #DC2626; }
  </style>
</head>
<body>

  <!-- OVERLAY LOADING -->
  <div id="loading-overlay">
    <h2 class="lo"><i class="fa-solid fa-graduation-cap"></i> Absensi.</h2>
    <div class="loader"></div>
  </div>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h2><i class="fa-solid fa-graduation-cap"></i> Absensi.</h2>
    <div class="admin-profile">
      <img src="<?= !empty($dataAdmin['foto']) ? htmlspecialchars($dataAdmin['foto']) : 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png'; ?>" 
           class="admin-avatar-img"
           onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png';">
      <div class="admin-info">
        <div class="admin-name"><?= htmlspecialchars($dataAdmin['nama']) ?></div>
        <div class="admin-role">@<?= htmlspecialchars($dataAdmin['username']) ?>terganteng</div>
      </div>
    </div>
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="tugas.php" class="<?= basename($_SERVER['PHP_SELF']) == 'tugas.php' ? 'active' : '' ?>">Tugas Sekolah</a>
    <a href="pengumuman.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pengumuman.php' ? 'active' : '' ?>">Pengumuman</a>
    <a href="cetak_kartu.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cetak_kartu.php' ? 'active' : '' ?>">Cetak Kartu</a>
    <a href="log.php" class="<?= basename($_SERVER['PHP_SELF']) == 'log.php' ? 'active' : '' ?>">Log Aktivitas</a>
    <a href="logout.php" style="color: #F87171; margin-top: auto;">Logout</a>
  </div>

  <!--  POP-UP  -->
  <div id="customModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-icon" id="modalIcon">❓</div>
      <h3 id="modalTitle">Konfirmasi</h3>
      <p id="modalMessage">Apakah Anda yakin?</p>
      <div class="modal-buttons">
        <button id="modalCancel" class="btn-cancel">Batal</button>
        <button id="modalConfirm" class="btn-confirm">Ya, Lanjutkan</button>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT (Dibuka di sini, ditutup di footer.php) -->
  <div class="main-content">