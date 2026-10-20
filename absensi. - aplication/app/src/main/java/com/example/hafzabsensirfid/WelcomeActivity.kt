package com.example.hafzabsensirfid

import android.app.Activity
import android.app.AlertDialog
import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class WelcomeActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_welcome)

        supportActionBar?.hide()

        val tvHari = findViewById<TextView>(R.id.tvHari)
        val tvTanggal = findViewById<TextView>(R.id.tvTanggal)
        val btnMulai = findViewById<Button>(R.id.btnMulai)
        val btnTentang = findViewById<Button>(R.id.btnTentang)

        // Ambil hari dan tanggal otomatis
        val localeID = Locale("id", "ID")
        val sdfHari = SimpleDateFormat("EEEE", localeID)
        val sdfTanggal = SimpleDateFormat("d MMMM yyyy", localeID)

        tvHari.text = sdfHari.format(Date())
        tvTanggal.text = sdfTanggal.format(Date())

        // Aksi saat tombol MULAI ABSEN diklik -> Buka Scanner QR
        btnMulai.setOnClickListener {
            val intent = Intent(this, QrScannerActivity::class.java)
            qrLauncher.launch(intent)
            overridePendingTransition(R.anim.slide_in_right, R.anim.fade_out)
        }

        // Aksi Tombol Tentang Aplikasi
        btnTentang.setOnClickListener {
            tampilkanTentangAplikasi()
        }
    }

    // Fungsi untuk memunculkan Pop-up Tentang Aplikasi
    private fun tampilkanTentangAplikasi() {
        val builder = AlertDialog.Builder(this)
        builder.setTitle("Tentang Aplikasi")
        builder.setMessage(
            "Aplikasi Absensi RFID\n" +
                    "Versi 1.0.0\n\n" +
                    "Dikembangkan oleh:\n" +
                    "M.Hafidz zaky al fajri\n\n" +
                    "Sistem absensi berbasis IoT yang terintegrasi dengan RFID yang terhubung dengan esp32 sebagai pembaca kartu rfid, dan juga mengandalkan nfc sebagai pembaca kartu untuk absensi siswa, dan juga dashboard web berfungsi juga untuk menampilkan data dan statistik para siswa saat absensi.\n\n" +
                    "© 2026 HafzDev. All rights reserved."
        )

        builder.setPositiveButton("Tutup") { dialog, _ ->
            dialog.dismiss()
        }

        val dialog = builder.create()
        dialog.show()
    }

    // Menerima hasil dari Scanner QR
    private val qrLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val qrData = result.data?.getStringExtra("QR_RESULT")
            if (qrData != null && qrData.contains("ABSEN_SEKOLAH")) {
                Toast.makeText(this, "QR Code Valid! Silakan tempelkan kartu.", Toast.LENGTH_LONG).show()
                startActivity(Intent(this, MainActivity::class.java))
                overridePendingTransition(R.anim.slide_in_right, R.anim.fade_out)
            } else {
                Toast.makeText(this, "QR Code Tidak Valid!", Toast.LENGTH_LONG).show()
            }
        }
    }
}