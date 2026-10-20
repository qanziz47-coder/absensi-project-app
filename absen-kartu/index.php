<?php
session_start();

// Jika admin sudah login, arahkan ke dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

// Jika belum login, arahkan ke halaman login
header("Location: login.php");
exit;
?>