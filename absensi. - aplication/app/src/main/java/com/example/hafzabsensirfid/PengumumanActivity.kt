package com.example.hafzabsensirfid

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

class PengumumanActivity : AppCompatActivity() {

    private lateinit var rvPengumuman: RecyclerView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_pengumuman)
        supportActionBar?.hide()

        rvPengumuman = findViewById(R.id.rvPengumuman)
        rvPengumuman.layoutManager = LinearLayoutManager(this)

        ambilData()
    }

    private fun ambilData() {
        val serverURL = "https://hafzdev.web.id/absen-kartu/api/pegumuman.php"

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val url = URL(serverURL)
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "GET"
                val responseString = conn.inputStream.bufferedReader().readText()
                conn.disconnect()

                val json = JSONObject(responseString)
                val list = mutableListOf<PengumumanData>()

                if (json.getBoolean("ada")) {
                    val arr = json.getJSONArray("data")
                    for (i in 0 until arr.length()) {
                        val item = arr.getJSONObject(i)
                        list.add(
                            PengumumanData(
                                judul = item.getString("judul"),
                                isi = item.getString("isi"),
                                tanggal = item.getString("tanggal")
                            )
                        )
                    }
                }

                withContext(Dispatchers.Main) {
                    rvPengumuman.adapter = PengumumanAdapter(list)
                }
            } catch (e: Exception) {
                // Abaikan error
            }
        }
    }
}