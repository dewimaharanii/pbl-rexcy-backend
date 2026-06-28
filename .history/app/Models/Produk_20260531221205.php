<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produksi';
    protected $primaryKey = 'Id_Produksi';
    public $timestamps = false;

    protected $fillable = [
        'Id_Produsen', 'Nama_Produk', 'Jumlah_Stok',
        'Harga_Produksi', 'Lokasi_Tangkap', 'Catatan', 'Dibuat_Pada'
    ];
}