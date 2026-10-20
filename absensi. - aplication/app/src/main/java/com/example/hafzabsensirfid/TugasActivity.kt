package com.example.hafzabsensirfid

import android.os.Bundle
import android.view.View
import android.widget.TextView
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

class TugasActivity : AppCompatActivity() {

    private lateinit var rvTugas: RecyclerView
    private lateinit var tvKosong: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_tugas)

        supportActionBar?.hide()

        rvTugas = findViewById(R.id.rvTugas)
        tvKosong = findViewById(R.id.tvKosong)

        rvTugas.layoutManager = LinearLayoutManager(this)

        ambilDataTugas()
    }

    private fun ambilDataTugas() {
        // ⚠️ PASTIKAN IP INI SESUAI DENGAN IP PC KAMU
        val serverURL = "https://hafzdev.web.id/absen-kartu/api/tugas.php"

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val url = URL(serverURL)
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "GET"
                val responseString = conn.inputStream.bufferedReader().readText()
                conn.disconnect()

                val json = JSONObject(responseString)
                val listTugas = mutableListOf<TugasData>()

                if (json.getBoolean("ada")) {
                    val dataArray = json.getJSONArray("data")
                    for (i in 0 until dataArray.length()) {
                        val item = dataArray.getJSONObject(i)
                        listTugas.add(
                            TugasData(
                                judul = item.getString("judul"),
                                mapel = item.getString("mata_pelajaran"),
                                kelas = item.getString("target_kelas"),
                                deskripsi = item.getString("deskripsi"),
                                deadline = item.getString("deadline")
                            )
                        )
                    }
                }

                withContext(Dispatchers.Main) {
                    if (listTugas.isEmpty()) {
                        tvKosong.visibility = View.VISIBLE
                        rvTugas.visibility = View.GONE
                    } else {
                        tvKosong.visibility = View.GONE
                        rvTugas.visibility = View.VISIBLE
                        rvTugas.adapter = TugasAdapter(listTugas)
                    }
                }

            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    tvKosong.visibility = View.VISIBLE
                    tvKosong.text = "Gagal memuat data. Cek koneksi."
                }
            }
        }
    }
}