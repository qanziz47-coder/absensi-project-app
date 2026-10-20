<?php

header('Content-Type: text/plain');
require_once '../config/database.php';

$uid = isset($_POST['uid']) ? trim($_POST['uid']) : '';

if(empty($uid)){
    echo "ERROR: uid kosong";
    exit;
}

// Cek apakah UID terdaftar dan ambil nama serta foto
$stmt = $conn->prepare("SELECT nama, kelas, foto FROM users WHERE uid = ? AND status = 'aktif'");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "DITOLAK: Kartu tidak terdaftar";
    exit;
}

$user = $result->fetch_assoc();
$nama = $user['nama'];
$kelas = $user['kelas'];
$foto = $user['foto'] ? $user['foto'] : "https://i.pravatar.cc/150";
$stmt->close();

$tanggal = date('Y-m-d');
$jam = date('H:i:s');

// absensi 

$stmt = $conn->prepare("SELECT id, status FROM absensi WHERE uid = ? AND tanggal = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('ss', $uid, $tanggal);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    if($row['status'] === 'masuk'){
        $stmt->close();
        $stmt = $conn->prepare("UPDATE absensi SET status = 'pulang' WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        echo "SUCCESS|$nama|$kelas|$foto|Absen masuk";
    }else{
        echo "SUCCESS|$nama|$kelas|$foto|Absen pulang";
    }
}else{
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO absensi (uid, nama, tanggal, jam, status) VALUES(?,?,?,?, 'masuk')");
    $stmt->bind_param('ssss', $uid, $nama, $tanggal, $jam);
    $stmt->execute();
    echo "SUCCESS: Absen masuk = $nama";
}
$stmt->close();
$conn->close();
?>