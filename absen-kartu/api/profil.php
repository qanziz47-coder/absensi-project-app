<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';

if (empty($uid)) {
    echo json_encode(['status' => 'error', 'message' => 'UID kosong']);
    exit;
}

$stmt = $conn->prepare("SELECT nama, kelas, foto FROM users WHERE uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'nama' => $user['nama'],
        'kelas' => $user['kelas'],
        'foto' => $user['foto'] ? $user['foto'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/220px-User_icon_2.svg.png',
        'uid' => $uid
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
}
$conn->close();
?>