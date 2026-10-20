<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$result = $conn->query("SELECT * FROM tugas ORDER BY id DESC");

if ($result->num_rows === 0) {
    echo json_encode(['ada' => false]);
    exit;
}

$tugas_list = [];
while ($row = $result->fetch_assoc()) {
    $tugas_list[] = [
        'id' => $row['id'],
        'judul' => $row['judul'],
        'deskripsi' => $row['deskripsi'],
        'mata_pelajaran' => $row['mata_pelajaran'],
        'target_kelas' => $row['target_kelas'],
        'deadline' => $row['deadline']
    ];
}

echo json_encode(['ada' => true, 'data' => $tugas_list]);
$conn->close();
?>