package com.example.hafzabsensirfid

import android.graphics.Bitmap
import android.os.Bundle
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.bumptech.glide.Glide
import com.google.zxing.BarcodeFormat
import com.google.zxing.MultiFormatWriter
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

class ProfilActivity : AppCompatActivity() {

    private lateinit var ivFotoProfil: ImageView
    private lateinit var ivQrProfil: ImageView
    private lateinit var tvNamaProfil: TextView
    private lateinit var tvKelasProfil: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_profil)
        supportActionBar?.hide()

        ivFotoProfil = findViewById(R.id.ivFotoProfil)
        ivQrProfil = findViewById(R.id.ivQrProfil)
        tvNamaProfil = findViewById(R.id.tvNamaProfil)
        tvKelasProfil = findViewById(R.id.tvKelasProfil)

        // Ambil UID terakhir dari SharedPreferences (disimpan saat scan)
        val sharedPref = getSharedPreferences("AbsensiPref", MODE_PRIVATE)
        val lastUid = sharedPref.getString("last_uid", "")

        if (lastUid.isNullOrEmpty()) {
            Toast.makeText(this, "Belum ada kartu yang di-scan.", Toast.LENGTH_LONG).show()
            tvNamaProfil.text = "Belum Ada Data"
        } else {
            ambilDataProfil(lastUid)
        }
    }

    private fun ambilDataProfil(uid: String) {
        // ⚠️ GANTI DENGAN DOMAIN KAMU NANTI
        val serverURL = "https://hafzdev.web.id/absen-kartu/api/profil.php?uid=$uid"

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val url = URL(serverURL)
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "GET"
                val responseString = conn.inputStream.bufferedReader().readText()
                conn.disconnect()

                val json = JSONObject(responseString)
                if (json.getString("status") == "success") {
                    val nama = json.getString("nama")
                    val kelas = json.getString("kelas")
                    val foto = json.getString("foto")

                    withContext(Dispatchers.Main) {
                        tvNamaProfil.text = nama
                        tvKelasProfil.text = kelas

                        // Load Foto
                        Glide.with(this@ProfilActivity)
                            .load(foto)
                            .placeholder(android.R.drawable.ic_menu_myplaces)
                            .into(ivFotoProfil)

                        // Generate QR Code UID
                        generateQrCode(uid)
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Toast.makeText(this@ProfilActivity, "Gagal memuat profil", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    // Fungsi membuat gambar QR Code
    private fun generateQrCode(text: String) {
        try {
            val bitMatrix = MultiFormatWriter().encode(text, BarcodeFormat.QR_CODE, 300, 300)
            val bitmap = Bitmap.createBitmap(300, 300, Bitmap.Config.RGB_565)
            for (x in 0 until 300) {
                for (y in 0 until 300) {
                    bitmap.setPixel(x, y, if (bitMatrix[x, y]) android.graphics.Color.BLACK else android.graphics.Color.WHITE)
                }
            }
            ivQrProfil.setImageBitmap(bitmap)
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }
}