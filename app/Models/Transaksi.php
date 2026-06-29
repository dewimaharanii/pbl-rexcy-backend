<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;
use App\Models\MitraHilir;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    
    // Karena di database kolom ID utamanya adalah 'id' (auto-increment)
    protected $primaryKey = 'id'; 
    
    protected $fillable = [
        'Id_Transaksi',
        'Id_Mitra',
        'Id_Produksi',
        'Jumlah',
        'Total_Harga',
        'Status',
        'Tanggal_Transaksi',
        'catatan',
        'nama_pemesan',
        'no_telp',
        'alamat_pemesan'
    ];

    // FIX 1: Tambahkan Relasi ke Produk agar Produsen tahu ini barang miliknya
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'Id_Produksi', 'Id_Produksi');
    }

    // FIX 2: Tambahkan Relasi ke Mitra Hilir agar Produsen tahu siapa pembelinya
    public function mitra()
    {
        return $this->belongsTo(MitraHilir::class, 'Id_Mitra', 'Id_Mitra');
    }
}