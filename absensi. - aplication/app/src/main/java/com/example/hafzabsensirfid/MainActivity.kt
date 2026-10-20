package com.example.hafzabsensirfid

import android.app.PendingIntent
import android.content.Intent
import android.media.AudioManager
import android.media.ToneGenerator
import android.nfc.NfcAdapter
import android.nfc.Tag
import android.os.Build
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.os.VibrationEffect
import android.os.Vibrator
import android.view.View
import android.widget.ImageView
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.cardview.widget.CardView
import com.bumptech.glide.Glide
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import org.json.JSONObject
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL
import android.widget.Button

class MainActivity : AppCompatActivity() {

    private lateinit var nfcAdapter: NfcAdapter
    private lateinit var tvStatus: TextView
    private lateinit var tvNamaUser: TextView
    private lateinit var tvJamStatus: TextView
    private lateinit var tvKeterangan: TextView
    private lateinit var ivFotoUser: ImageView

    private val handler = Handler(Looper.getMainLooper())
    private var lastAbsenId = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        val btnProfil = findViewById<Button>(R.id.btnProfil)
        btnProfil.setOnClickListener {
            startActivity(Intent(this, ProfilActivity::class.java))
        }

        // Aksi Tombol Logout di Navbar
        val btnLogout = findViewById<ImageView>(R.id.btnLogout)
        btnLogout.setOnClickListener {
            val intent = Intent(this, WelcomeActivity::class.java)
            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            startActivity(intent)
            finish()
            overridePendingTransition(R.anim.fade_in, R.anim.fade_out)
        }

        // Inisialisasi View Teks & Foto
        tvStatus = findViewById(R.id.tvStatus)
        tvNamaUser = findViewById(R.id.tvNamaUser)
        tvJamStatus = findViewById(R.id.tvJamStatus)
        tvKeterangan = findViewById(R.id.tvKeterangan)
        ivFotoUser = findViewById(R.id.ivFotoUser)

        // Set Tanggal Otomatis
        val tvTanggal = findViewById<TextView>(R.id.tvTanggal)
        val localeID = java.util.Locale("id", "ID")
        val sdf = java.text.SimpleDateFormat("EEEE, dd/MM/yyyy", localeID)
        tvTanggal.text = sdf.format(java.util.Date())

        nfcAdapter = NfcAdapter.getDefaultAdapter(this)

        // --- MENU CARD SETUP ---
        val cardPengumuman = findViewById<CardView>(R.id.cardPengumuman)
        val cardTugas = findViewById<CardView>(R.id.cardTugas)
        val cardScan = findViewById<CardView>(R.id.cardScan)

        // Aksi Klik Kartu Pengumuman
        cardPengumuman.setOnClickListener {
            startActivity(Intent(this, PengumumanActivity::class.java))
        }

        // Aksi Klik Kartu Tugas
        cardTugas.setOnClickListener {
            startActivity(Intent(this, TugasActivity::class.java))
        }

        // Aksi Klik Kartu Scan (Reset Tampilan)
        cardScan.setOnClickListener {
            resetTampilan()
        }

        // Jika HP tidak mendukung NFC, buat kartu Scan menjadi transparan
        if (nfcAdapter == null) {
            cardScan.isEnabled = false
            cardScan.alpha = 0.5f
        }

        mulaiPantauAbsen()
    }

    private fun resetTampilan() {
        tvStatus.text = "Silakan Tempelkan Kartu"
        ivFotoUser.visibility = View.INVISIBLE
        tvNamaUser.text = "Menunggu Kartu..."
        tvJamStatus.visibility = View.INVISIBLE
        tvKeterangan.visibility = View.INVISIBLE
    }

    override fun onResume() {
        super.onResume()
        val intent = Intent(this, javaClass).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP)
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent, PendingIntent.FLAG_MUTABLE
        )
        nfcAdapter?.enableForegroundDispatch(this, pendingIntent, null, null)
    }

    override fun onPause() {
        super.onPause()
        nfcAdapter?.disableForegroundDispatch(this)
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)

        val tag: Tag? = intent.getParcelableExtra(NfcAdapter.EXTRA_TAG)
        if (tag != null) {
            val uidBytes = tag.id
            val uidStr = uidBytes.joinToString(" ") { String.format("%02X", it) }

            // Simpan UID terakhir untuk halaman profil
            val sharedPref = getSharedPreferences("AbsensiPref", MODE_PRIVATE)
            sharedPref.edit().putString("last_uid", uidStr).apply()

            tvStatus.text = "Membaca Kartu..."
            bunyiDanGetar()
            kirimKeServer(uidStr)
        }
    }

    private fun kirimKeServer(uid: String) {
        val serverURL = "https://hafzdev.web.id/absen-kartu/api/absen.php"

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val url = URL(serverURL)
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "POST"
                conn.doOutput = true
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded")

                val postData = "uid=$uid"
                val outputStream = OutputStreamWriter(conn.outputStream)
                outputStream.write(postData)
                outputStream.flush()
                outputStream.close()

                val responseCode = conn.responseCode
                val responseString = conn.inputStream.bufferedReader().readText()

                withContext(Dispatchers.Main) {
                    if (responseCode == 200 && responseString.contains("SUCCESS")) {
                        val parts = responseString.split("|")
                        val namaUser = if (parts.size > 1) parts[1] else "User"
                        val kelasUser = if (parts.size > 2) parts[2] else "-"
                        val fotoUrl = if (parts.size > 3) parts[3] else ""
                        val statusAbsen = if (parts.size > 4) parts[4] else "Absen"

                        val jamSekarang = java.text.SimpleDateFormat("HH:mm:ss", java.util.Locale.getDefault()).format(java.util.Date())

                        tampilkanDataLengkap(namaUser, kelasUser, fotoUrl, jamSekarang, statusAbsen)

                    } else {
                        tvStatus.text = "AKSES DITOLAK"
                        tvNamaUser.text = "Kartu Tidak Terdaftar"
                        ivFotoUser.visibility = View.INVISIBLE
                        tvJamStatus.visibility = View.INVISIBLE
                        tvKeterangan.visibility = View.INVISIBLE
                    }
                }
                conn.disconnect()
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    tvStatus.text = "Gagal terhubung ke Server PC"
                    ivFotoUser.visibility = View.INVISIBLE
                    tvNamaUser.text = "Koneksi Error"
                    tvJamStatus.visibility = View.INVISIBLE
                    tvKeterangan.visibility = View.INVISIBLE
                }
            }
        }
    }

    private fun mulaiPantauAbsen() {
        handler.postDelayed(object : Runnable {
            override fun run() {
                cekServerTerbaru()
                handler.postDelayed(this, 3000)
            }
        }, 3000)
    }

    private fun cekServerTerbaru() {
        val serverURL = "https://hafzdev.web.id/absen-kartu/api/absen.php"
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val url = URL(serverURL)
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "GET"
                val responseString = conn.inputStream.bufferedReader().readText()
                conn.disconnect()

                val json = JSONObject(responseString)
                if (json.getBoolean("ada")) {
                    val idBaru = json.getInt("id")

                    if (idBaru > lastAbsenId) {
                        lastAbsenId = idBaru

                        val nama = json.optString("nama", "User")
                        val kelas = json.optString("kelas", "-")
                        val foto = json.optString("foto", "")
                        val jam = json.optString("jam", "--:--")
                        val status = json.optString("status", "Absen")

                        withContext(Dispatchers.Main) {
                            tampilkanDataLengkap(nama, kelas, foto, jam, status)
                        }
                    }
                }
            } catch (e: Exception) {
                // Abaikan error jaringan saat polling
            }
        }
    }

    private fun tampilkanDataLengkap(nama: String, kelas: String, fotoUrl: String, jam: String, statusAbsen: String) {
        tvNamaUser.text = "$nama\n($kelas)"
        tvStatus.text = "ABSEN BERHASIL"

        tvJamStatus.text = jam
        tvKeterangan.text = statusAbsen

        if (statusAbsen.contains("pulang", ignoreCase = true)) {
            tvKeterangan.setTextColor(android.graphics.Color.parseColor("#FF9800"))
        } else {
            tvKeterangan.setTextColor(android.graphics.Color.parseColor("#4CAF50"))
        }

        ivFotoUser.visibility = View.VISIBLE
        ivFotoUser.setColorFilter(null)

        if (fotoUrl.isNotEmpty()) {
            Glide.with(this@MainActivity)
                .load(fotoUrl)
                .placeholder(android.R.drawable.ic_menu_myplaces)
                .error(android.R.drawable.ic_menu_myplaces)
                .into(ivFotoUser)
        } else {
            ivFotoUser.setImageResource(android.R.drawable.ic_menu_myplaces)
        }

        tvJamStatus.visibility = View.VISIBLE
        tvKeterangan.visibility = View.VISIBLE

        bunyiDanGetar()
    }

    private fun bunyiDanGetar() {
        val toneG = ToneGenerator(AudioManager.STREAM_NOTIFICATION, 100)
        toneG.startTone(ToneGenerator.TONE_PROP_BEEP, 200)

        val vibrator = getSystemService(VIBRATOR_SERVICE) as Vibrator
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            vibrator.vibrate(VibrationEffect.createOneShot(500, VibrationEffect.DEFAULT_AMPLITUDE))
        } else {
            @Suppress("DEPRECATION")
            vibrator.vibrate(500)
        }
    }

    @Deprecated("Deprecated in Java")
    override fun onBackPressed() {
        super.onBackPressed()
        overridePendingTransition(R.anim.fade_in, R.anim.slide_out_right)
    }
}