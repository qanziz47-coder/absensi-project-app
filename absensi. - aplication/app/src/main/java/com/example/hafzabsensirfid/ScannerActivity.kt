package com.example.hafzabsensirfid

import android.app.PendingIntent
import android.content.Intent
import android.content.IntentFilter
import android.media.AudioManager
import android.media.ToneGenerator
import android.nfc.NfcAdapter
import android.nfc.Tag
import android.nfc.tech.NfcA
import android.os.Build
import android.os.Bundle
import android.os.VibrationEffect
import android.os.Vibrator
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL

class ScannerActivity : AppCompatActivity() {

    private lateinit var nfcAdapter: NfcAdapter
    private lateinit var tvStatus: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_scanner)

        supportActionBar?.title = "Scan Kartu RFID"
        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        tvStatus = findViewById(R.id.tvStatus)
        nfcAdapter = NfcAdapter.getDefaultAdapter(this)

        if (nfcAdapter == null) {
            Toast.makeText(this, "HP ini tidak mendukung NFC", Toast.LENGTH_LONG).show()
            finish()
        }
    }

    override fun onResume() {
        super.onResume()
        // Aktifkan pembaca NFC saat layar ini dibuka
        val intent = Intent(this, javaClass).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP)
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent, PendingIntent.FLAG_MUTABLE
        )
        nfcAdapter.enableForegroundDispatch(this, pendingIntent, null, null)
    }

    override fun onPause() {
        super.onPause()
        // Matikan pembaca NFC saat layar ditutup
        nfcAdapter.disableForegroundDispatch(this)
    }

    // Fungsi ini otomatis terpanggil saat ada kartu ditempelkan
    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)

        val tag: Tag? = intent.getParcelableExtra(NfcAdapter.EXTRA_TAG)
        if (tag != null) {
            val uidBytes = tag.id
            val uidStr = uidBytes.joinToString(" ") { String.format("%02X", it) }

            tvStatus.text = "Membaca: $uidStr"
            bunyiDanGetar()
            kirimKeServer(uidStr)
        }
    }

    private fun kirimKeServer(uid: String) {
        // GANTI IP INI dengan IP PC KAMU
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

                withContext(Dispatchers.Main) {
                    if (responseCode == 200) {
                        tvStatus.text = "✅ Absen Berhasil!"
                        Toast.makeText(this@ScannerActivity, "Data terkirim ke server", Toast.LENGTH_SHORT).show()
                    } else {
                        tvStatus.text = "❌ Gagal kirim (Error $responseCode)"
                    }
                }
                conn.disconnect()
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    tvStatus.text = "❌ Gagal terhubung ke server"
                    Toast.makeText(this@ScannerActivity, "Cek koneksi WiFi", Toast.LENGTH_SHORT).show()
                }
            }
        }
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

    override fun onSupportNavigateUp(): Boolean {
        finish()
        return true
    }
}