<?php
session_start();
require_once 'config/database.php';

// Proteksi: Hanya admin yang login yang bisa mengunduh
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua data absensi (bisa difilter berdasarkan bulan jika perlu)
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query = "SELECT a.tanggal, a.jam, a.nama, a.uid, a.status, u.kelas 
          FROM absensi a 
          LEFT JOIN users u ON a.uid = u.uid 
          WHERE MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'
          ORDER BY a.tanggal ASC, a.jam ASC";
$result = $conn->query($query);

// Header untuk memaksa browser mengunduh file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_Absensi_' . $bulan . '_' . $tahun . '.csv"');

// Buka output stream
$output = fopen('php://output', 'w');

// Tambahkan BOM UTF-8 agar karakter terbaca di Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header Kolom Excel
fputcsv($output, array('Tanggal', 'Jam', 'Nama Siswa', 'UID Kartu', 'Kelas', 'Status'));

// Isi Data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['tanggal'],
            $row['jam'],
            $row['nama'],
            $row['uid'],
            $row['kelas'] ? $row['kelas'] : '-',
            strtoupper($row['status'])
        ));
    }
}

fclose($output);
exit;
?>