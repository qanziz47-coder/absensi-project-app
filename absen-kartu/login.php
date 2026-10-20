<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);

    $query = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_nama'] = $admin['nama'];
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin - Absensi RFID</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
    body { background: linear-gradient(135deg, #1E1B4B 0%, #4F46E5 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
    .login-card { background: #FFF; padding: 40px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
    .login-card h1 { text-align: center; color: #1E1B4B; margin-bottom: 10px; font-size: 26px; }
    .login-card p { text-align: center; color: #6B7280; margin-bottom: 30px; font-size: 14px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
    .form-group input { width: 100%; padding: 14px 16px; border: 1px solid #E5E7EB; border-radius: 10px; font-size: 14px; background: #F9FAFB; }
    .form-group input:focus { outline: none; border-color: #4F46E5; background: #FFF; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .btn-login { width: 100%; padding: 14px; background: #4F46E5; color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 16px; cursor: pointer; }
    .btn-login:hover { background: #4338CA; }
    .error-msg { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 8px; font-size: 13px; text-align: center; margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="login-card">
    <h1>Login</h1>
    <p>Selamat datang di Absesni.</p>
    <?php if ($error): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>
      </div>
      <button type="submit" name="login" class="btn-login">Masuk</button>
    </form>
  </div>
</body>
</html>