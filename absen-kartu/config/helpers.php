<?php
// Fungsi untuk mencatat log aktivitas ke database
function catatLog($conn, $aksi) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Pastikan admin sudah login
    if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_nama'])) {
        $admin_id = $_SESSION['admin_id'];
        $nama_admin = $conn->real_escape_string($_SESSION['admin_nama']);
        $aksi = $conn->real_escape_string($aksi);
        
        $sql = "INSERT INTO log_aktivitas (admin_id, nama_admin, aksi) VALUES ('$admin_id', '$nama_admin', '$aksi')";
        $conn->query($sql);
    }
}
?>