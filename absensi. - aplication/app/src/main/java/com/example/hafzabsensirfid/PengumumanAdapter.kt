package com.example.hafzabsensirfid

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

class PengumumanAdapter(private val list: List<PengumumanData>) : RecyclerView.Adapter<PengumumanAdapter.ViewHolder>() {

    class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val tvJudul: TextView = view.findViewById(R.id.tvJudulPengumuman)
        val tvTanggal: TextView = view.findViewById(R.id.tvTanggalPengumuman)
        val tvIsi: TextView = view.findViewById(R.id.tvIsiPengumuman)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context).inflate(R.layout.item_pengumuman, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val item = list[position]
        holder.tvJudul.text = item.judul
        holder.tvTanggal.text = item.tanggal
        holder.tvIsi.text = item.isi
    }

    override fun getItemCount() = list.size
}

data class PengumumanData(
    val judul: String,
    val isi: String,
    val tanggal: String
)