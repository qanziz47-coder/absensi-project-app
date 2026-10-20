<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Ambil 1 data absensi terbaru, GABUNGKAN dengan users untuk ambil kelas & foto
$result = $conn->query("SELECT a.id, a.uid, a.nama, a.tanggal, a.jam, a.status, u.kelas, u.foto 
                        FROM absensi a 
                        LEFT JOIN users u ON a.uid = u.uid 
                        ORDER BY a.id DESC LIMIT 1");

if ($result->num_rows === 0) {
    echo json_encode(['ada' => false]);
    exit;
}

$row = $result->fetch_assoc();
echo json_encode([
    'ada' => true,
    'id' => $row['id'],
    'uid' => $row['uid'],
    'nama' => $row['nama'],
    'kelas' => $row['kelas'],
    'tanggal' => $row['tanggal'],
    'jam' => $row['jam'],
    'status' => $row['status'],
    'foto' => $row['foto']
]);
$conn->close();
?>