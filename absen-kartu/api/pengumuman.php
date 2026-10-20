<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$result = $conn->query("SELECT * FROM pengumuman ORDER BY id DESC");

if ($result->num_rows === 0) {
    echo json_encode(['ada' => false]);
    exit;
}

$list = [];
while ($row = $result->fetch_assoc()) {
    $list[] = [
        'id' => $row['id'],
        'judul' => $row['judul'],
        'isi' => $row['isi'],
        'tanggal' => $row['created_at']
    ];
}

echo json_encode(['ada' => true, 'data' => $list]);
$conn->close();
?>