  </div> <!-- Penutup main-content -->

  <script>
    // Jam Real-time
    function updateClock() {
      const clockElement = document.getElementById('clock');
      if (clockElement) {
        const now = new Date();
        clockElement.textContent = 
          String(now.getHours()).padStart(2, '0') + ':' + 
          String(now.getMinutes()).padStart(2, '0') + ':' + 
          String(now.getSeconds()).padStart(2, '0');
      }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Logika Menyembunyikan Overlay Loading
    window.addEventListener('load', function() {
      const overlay = document.getElementById('loading-overlay');
      const durasiTampil = 1600; 

      setTimeout(() => {
        if (overlay) {
          overlay.classList.add('hidden');
        }
      }, durasiTampil);
    });

        // --- FUNGSI POP-UP KUSTOM ---
    // Fungsi ini menggantikan alert() dan confirm() bawaan browser
    function showModal(title, message, type, callback) {
      const modal = document.getElementById('customModal');
      const icon = document.getElementById('modalIcon');
      const titleEl = document.getElementById('modalTitle');
      const msgEl = document.getElementById('modalMessage');
      const btnConfirm = document.getElementById('modalConfirm');
      const btnCancel = document.getElementById('modalCancel');

      titleEl.innerText = title;
      msgEl.innerText = message;

      // Atur ikon dan warna tombol berdasarkan tipe
      if (type === 'danger') {
        icon.innerText = '⚠️';
        btnConfirm.style.background = '#EF4444';
        btnCancel.style.display = 'block';
      } else if (type === 'success') {
        icon.innerText = '✅';
        btnConfirm.style.background = '#10B981';
        btnCancel.style.display = 'none';
      } else {
        icon.innerText = '❓';
        btnConfirm.style.background = '#4F46E5';
        btnCancel.style.display = 'block';
      }

      modal.classList.add('show');

      // Tombol Konfirmasi
      btnConfirm.onclick = function() {
        modal.classList.remove('show');
        if (callback) callback(true);
      };

      // Tombol Batal
      btnCancel.onclick = function() {
        modal.classList.remove('show');
        if (callback) callback(false);
      };
    }
  </script>
</body>
</html>