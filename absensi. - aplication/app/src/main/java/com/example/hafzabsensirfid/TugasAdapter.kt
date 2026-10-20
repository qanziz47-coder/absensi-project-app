package com.example.hafzabsensirfid

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

class TugasAdapter(private val listTugas: List<TugasData>) : RecyclerView.Adapter<TugasAdapter.ViewHolder>() {

    class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val tvJudul: TextView = view.findViewById(R.id.tvJudul)
        val tvMapel: TextView = view.findViewById(R.id.tvMapel)
        val tvKelas: TextView = view.findViewById(R.id.tvKelas)
        val tvDeskripsi: TextView = view.findViewById(R.id.tvDeskripsi)
        val tvDeadline: TextView = view.findViewById(R.id.tvDeadline)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context).inflate(R.layout.item_tugas, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val tugas = listTugas[position]
        holder.tvJudul.text = tugas.judul
        holder.tvMapel.text = tugas.mapel
        holder.tvKelas.text = tugas.kelas
        holder.tvDeskripsi.text = tugas.deskripsi
        holder.tvDeadline.text = "Deadline: ${tugas.deadline}"
    }

    override fun getItemCount() = listTugas.size
}

// Data Class untuk menampung data tugas
data class TugasData(
    val judul: String,
    val mapel: String,
    val kelas: String,
    val deskripsi: String,
    val deadline: String
)