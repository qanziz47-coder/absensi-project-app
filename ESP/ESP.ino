#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// ==== Konfigurasi WiFi & Server ====
const char* ssid      = "HAIFAF5";       
const char* password  = "F5hafifa"; 
const char* serverURL = "https://hafzdev.web.id/absen-kartu/api/absen.php"; 

// ==== Pin ====
#define SS_PIN   5
#define RST_PIN  27
#define OLED_SDA 16
#define OLED_SCL 21

// ==== OLED ====
#define SCREEN_WIDTH  128
#define SCREEN_HEIGHT 64
#define OLED_RESET    -1
#define OLED_ADDR     0x3C

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
MFRC522 rfid(SS_PIN, RST_PIN);

// ==== Variabel Animasi Teks Berjalan ====
int posisiTeksX = SCREEN_WIDTH;
unsigned long waktuTerakhirGerak = 0;
unsigned long waktuMulai = 0; // Untuk simulasi jam

// ==== Setup ====
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n=== Absensi RFID Booting ===");

  // Inisialisasi OLED
  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, OLED_ADDR)) {
    Serial.println("❌ OLED gagal!");
  } else {
    Serial.println("✅ OLED OK");
    tampilOLED("Absensi RFID", "Booting...");
  }

  // Inisialisasi RFID
  SPI.begin();
  rfid.PCD_Init();
  delay(100);
  byte versi = rfid.PCD_ReadRegister(MFRC522::VersionReg);
  Serial.print("Versi RC522: 0x");
  Serial.println(versi, HEX);

  // Koneksi WiFi
  tampilOLED("Konek WiFi...", ssid);
  WiFi.begin(ssid, password);
  Serial.print("Konek WiFi");

  int timeout = 0;
  while (WiFi.status() != WL_CONNECTED && timeout < 30) {
    delay(500);
    Serial.print(".");
    timeout++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi OK");
    Serial.print("🌐 IP: ");
    Serial.println(WiFi.localIP());
    tampilOLED("WiFi OK", WiFi.localIP().toString());
    delay(1500);
    
    waktuMulai = millis(); // Mulai hitung jam
  } else {
    Serial.println("\n❌ WiFi GAGAL");
    tampilOLED("WiFi GAGAL", "Cek SSID/Password");
  }
}

// ==== Loop ====
void loop() {
  // --- 1. ANIMASI JAM & TEKS BERJALAN ---
  if (millis() - waktuTerakhirGerak > 50) {
    waktuTerakhirGerak = millis();
    
    // Hanya render jika tidak sedang scan
    if (!rfid.PICC_IsNewCardPresent()) {
      gambarJamDanTeks();
      display.display();
    }
  }

  // --- 2. DETEKSI KARTU RFID ---
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  // Baca UID
  String uidStr = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uidStr += "0";
    uidStr += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) uidStr += " ";
  }
  uidStr.toUpperCase();

  Serial.println("UID: " + uidStr);
  tampilOLED("Scan terdeteksi", uidStr);
  delay(800);

  // --- 3. KIRIM KE SERVER ---
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClientSecure client;
    client.setInsecure(); // Agar tidak perlu sertifikat manual
    
    HTTPClient http;
    http.begin(client, serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String data = "uid=" + uidStr;
    int httpCode = http.POST(data);

    if (httpCode > 0) {
      String response = http.getString();
      Serial.println("Server: " + response);

      if (response.indexOf("SUCCESS") >= 0) {
        int dashIdx = response.lastIndexOf("-");
        String nama = (dashIdx > 0) ? response.substring(dashIdx + 1) : "Berhasil";
        nama.trim();
        
        tampilkanAnimasiSukses(nama);
        
      } else {
        tampilOLED("AKSES DITOLAK", "Kartu tidak terdaftar");
        delay(2500);
      }
    } else {
      Serial.println("❌ Gagal kirim: " + String(httpCode));
      tampilOLED("Server error", "Cek koneksi");
      delay(2000);
    }
    http.end();
  } else {
    tampilOLED("WiFi terputus", "Cek koneksi");
    delay(2000);
  }

  // Reset posisi teks berjalan
  posisiTeksX = SCREEN_WIDTH;
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  delay(500);
}

// ==== Fungsi Tampil Teks OLED Biasa ====
void tampilOLED(String baris1, String baris2) {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);
  display.setCursor(0, 10);
  display.println(baris1);
  display.setCursor(0, 30);
  display.println(baris2);
  display.display();
}

// ==== Fungsi Menggambar Jam & Teks Berjalan ====
void gambarJamDanTeks() {
  display.clearDisplay();
  
  // 1. Hitung Waktu (Simulasi Jam)
  unsigned long detikBerjalan = (millis() - waktuMulai) / 1000;
  int jam = (detikBerjalan / 3600) % 24;
  int menit = (detikBerjalan / 60) % 60;
  int detik = detikBerjalan % 60;
  
  // Format Jam (HH:MM:SS)
  char waktuStr[10];
  sprintf(waktuStr, "%02d:%02d:%02d", jam, menit, detik);
  
  // 2. Gambar Jam Besar di Tengah
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(3);
  int16_t x1, y1;
  uint16_t w, h;
  display.getTextBounds(waktuStr, 0, 0, &x1, &y1, &w, &h);
  display.setCursor((SCREEN_WIDTH - w) / 2, 10); // Posisi tengah
  display.print(waktuStr);
  
  // 3. Garis Pemisah
  display.drawLine(10, 40, SCREEN_WIDTH - 10, 40, SSD1306_WHITE);
  
  // 4. Teks Berjalan di Bawah
  display.setTextSize(1);
  display.setCursor(posisiTeksX, 50);
  display.print("SILAKAN TEMPELKAN KARTU ANDA");
  
  // Geser teks ke kiri
  posisiTeksX -= 2; 
  
  // Jika teks sudah keluar layar, kembalikan ke kanan
  if (posisiTeksX < -180) { // Sesuaikan angka ini dengan panjang teks
    posisiTeksX = SCREEN_WIDTH;
  }
}

// ==== Fungsi Animasi Sukses (Kedip & Nama) ====
void tampilkanAnimasiSukses(String nama) {
  // Animasi Kedip 3x
  for (int i = 0; i < 3; i++) {
    display.clearDisplay();
    display.setTextColor(SSD1306_WHITE);
    display.setTextSize(2);
    display.setCursor(10, 10);
    display.println("ABSEN");
    display.setCursor(10, 35);
    display.println("BERHASIL!");
    display.display();
    delay(200);
    
    display.clearDisplay();
    display.display();
    delay(200);
  }
  
  // Tampilkan Nama User
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);
  display.setCursor(10, 10);
  display.println("Selamat datang,");
  
  display.setTextSize(2);
  display.setCursor(10, 30);
  display.println(nama);
  display.display();
  delay(2000);
}