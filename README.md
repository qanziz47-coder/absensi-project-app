# 📋 Sistem Absensi RFID Berbasis IoT

>Sistem absensi sekolah modern yang mengintegrasikan perangkat keras (ESP32) dengan aplikasi mobile (Android) dan dashboard web (PHP).

## ✨ Fitur Utama

*   **📡 Alat Absensi (ESP32 + RFID):** 
    *   Membaca kartu RFID untuk absensi.
    *   Menampilkan animasi Mata Robot (Kotak) dan Jam Digital di layar OLED.
    *   Mengirim data langsung ke server via WiFi (HTTPS).
*   **📱 Aplikasi Android (Native):**
    *   Absensi mandiri menggunakan NFC atau memindai QR Code (Anti-Titip Absen).
    *   Melihat daftar Tugas dan Pengumuman dari guru.
    *   Kartu Pelajar Digital (Profil & QR Code).
*   **💻 Web Dashboard Admin:**
    *   Login Admin yang aman.
    *   Panggilan Suara (Text-to-Speech) otomatis saat siswa absen.
    *   Manajemen Data Siswa, Tugas, dan Pengumuman.
    *   Cetak Kartu Pelajar (Download PNG).
    *   Ekspor Laporan Absensi ke Excel (CSV).
    *   Log Aktivitas Admin (dengan sistem reset ber-password).
*   **☁️ Infrastruktur & Keamanan:**
    *   Dapat diakses dari mana saja menggunakan **Cloudflare Tunnel**.
    *   Domain kustom: `hafzdev.web.id`.
    *   Koneksi terenkripsi HTTPS.

## 🛠️ Teknologi yang Digunakan

*   **Hardware:** ESP32, Modul RFID RC522, Layar OLED SSD1306.
*   **Backend Web:** PHP (Native), MySQL, Apache (XAMPP).
*   **Frontend Web:** HTML, CSS (Custom), JavaScript (Vanilla).
*   **Aplikasi Mobile:** Android Studio, Kotlin.
*   **Cloud & Networking:** Cloudflare Tunnel, Domain IDwebhost.

## 📂 Struktur Folder

*   `/Web` : Berisi kode sumber website (Dashboard Admin, API, dan Portofolio).
*   `/Android` : Berisi kode sumber aplikasi Android (Project Android Studio).
*   `/ESP32` : Berisi kode program Arduino (C++) untuk alat absensi.

## 🚀 Cara Menjalankan (Instalasi)

### 1. Persiapan Web & Database
1. Install XAMPP dan jalankan Apache & MySQL.
2. Buat database baru bernama `absensi_rfid`.
3. Import file `database.sql` (jika ada) atau buat tabel manual sesuai dokumentasi.
4. Copy folder `Web/absen-kartu` ke dalam `C:\xampp\htdocs\`.
5. Buka browser, akses `http://localhost/absen-kartu/`.

### 2. Menjalankan Aplikasi Android
1. Buka folder `Android` menggunakan Android Studio.
2. Ubah `baseURL` atau IP di file `MainActivity.kt` (sesuaikan dengan IP PC atau domain kalian).
3. Build dan Run ke HP Android.

### 3. Menjalankan Alat ESP32
1. Buka file `.ino` di Arduino IDE.
2. Ubah konfigurasi `ssid`, `password`, dan `serverURL` (sesuaikan dengan WiFi dan domain kalian).
3. Upload ke board ESP32.

## 📸 Dokumentasi 

*(Tempatkan gambar-gambar screenshot di sini. Kamu bisa upload gambar ke GitHub, lalu paste link-nya di bawah ini)*

**Dashboard Admin:**
![Dashboard Admin](link_gambar_dashboard_kamu)

**Aplikasi Android:**
![Aplikasi Android](link_gambar_aplikasi_kamu)

**Alat ESP32:**
![Alat ESP32](link_gambar_esp32_kamu)

## 👨‍💻 Pengembang

*   **Nama:** M.hafidz zaky al fajri
*   **Kelas:** 11 pplg 1
*   **GitHub:** qanziz47-coder
