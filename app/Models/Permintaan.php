<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;
use App\Models\MitraHilir;

class Permintaan extends Model
{
    protected $table = 'permintaan';
    protected $primaryKey = 'Id_Permintaan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'Id_Permintaan', 'Id_Mitra', 'Id_Produksi', 
        'Jumlah_Diminta', 'Status', 'Tanggal_Permintaan', 'Catatan',
        'nama_pemesan', 'no_telp', 'alamat_pemesan'
    ];

    // Relasi ke Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'Id_Produksi', 'Id_Produksi');
    }

    // Relasi ke Mitra Hilir
    public function mitra()
    {
        return $this->belongsTo(MitraHilir::class, 'Id_Mitra', 'Id_Mitra');
    }
}