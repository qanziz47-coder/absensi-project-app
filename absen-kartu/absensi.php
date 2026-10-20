
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hafz | Absensi RFID</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .container {
      background: white;
      border-radius: 20px;
      padding: 40px 30px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      text-align: center;
    }
    h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
    .subtitle { color: #888; font-size: 14px; margin-bottom: 30px; }
    .icon-idle {
      font-size: 80px;
      margin: 20px 0;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.1); opacity: 0.7; }
    }
    .checkmark {
      width: 120px;
      height: 120px;
      margin: 20px auto;
      display: none;
      animation: popIn 0.5s ease-out;
    }
    .checkmark.show { display: block; }
    @keyframes popIn {
      0% { transform: scale(0); opacity: 0; }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); opacity: 1; }
    }
    .checkmark circle {
      stroke: #4CAF50;
      stroke-width: 4;
      fill: none;
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    @keyframes stroke { 100% { stroke-dashoffset: 0; } }
    .checkmark path {
      stroke: #4CAF50;
      stroke-width: 5;
      stroke-linecap: round;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;
    }
    .result { display: none; animation: fadeIn 0.5s ease-in; }
    .result.show { display: block; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .status-text { font-size: 22px; font-weight: bold; color: #4CAF50; margin: 10px 0; }
    .nama { font-size: 28px; font-weight: bold; color: #333; margin: 15px 0; }
    .info { color: #666; font-size: 16px; margin: 8px 0; }
    .clock { font-size: 32px; font-weight: bold; color: #667eea; margin-top: 20px; }
    .footer { margin-top: 20px; color: #aaa; font-size: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>📋 Absensi RFID</h1>
    <p class="subtitle">Sistem Absensi Otomatis</p>

    <!-- Tampilan idle -->
    <div id="idle">
      <div class="icon-idle">📡</div>
      <p style="color:#666;">Silakan tempelkan kartu Anda</p>
      <div class="clock" id="clock">--:--:--</div>
    </div>

    <!-- Tampilan hasil scan -->
    <div id="result" class="result">
      <svg class="checkmark" id="checkmark" viewBox="0 0 52 52">
        <circle cx="26" cy="26" r="25"/>
        <path d="M14 27 l8 8 l16 -16"/>
      </svg>
      <div class="status-text" id="statusText">ABSEN BERHASIL</div>
      <div class="nama" id="nama">-</div>
      <div class="info" id="jam">-</div>
      <div class="info" id="tanggal">-</div>
    </div>

    <div class="footer">Hafz | Absensi RFID &copy; 2025</div>
  </div>

  <script>
    let lastID = 0;

    // Jam real-time
    function updateJam() {
      const sekarang = new Date();
      const jam = String(sekarang.getHours()).padStart(2, '0');
      const menit = String(sekarang.getMinutes()).padStart(2, '0');
      const detik = String(sekarang.getSeconds()).padStart(2, '0');
      document.getElementById('clock').textContent = jam + ':' + menit + ':' + detik;
    }
    setInterval(updateJam, 1000);
    updateJam();

    // Polling ke server tiap 1 detik
    function cekScanTerbaru() {
      fetch('api/latest.php?t=' + Date.now())  
        .then(res => res.json())
        .then(data => {
          if (data.ada && data.id > lastID) {
            lastID = data.id;
            tampilkanHasil(data);              
          }
        })
        .catch(err => console.log('Error:', err));
    }

    // 3. Tampilkan animasi
    function tampilkanHasil(data) {
      document.getElementById('idle').style.display = 'none';
      document.getElementById('result').classList.add('show');
      document.getElementById('checkmark').classList.add('show');

      document.getElementById('statusText').textContent = 'ABSEN BERHASIL';
      document.getElementById('nama').textContent = data.nama;
      document.getElementById('jam').textContent = '🕐 ' + data.jam;
      document.getElementById('tanggal').textContent = '📅 ' + data.tanggal;
        if (window.AndroidInterface) {
          window.AndroidInterface.bunyikanSukses();
      }

      setTimeout(() => {
        document.getElementById('result').classList.remove('show');
        document.getElementById('checkmark').classList.remove('show');
        document.getElementById('idle').style.display = 'block';
      }, 5000);
    }

    // Jalankan polling
    setInterval(cekScanTerbaru, 1000);
  </script>
</body>
</html>