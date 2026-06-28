<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokProduk extends Model
{
    protected $table = 'stok_produk';
    protected $primaryKey = 'id_stok';
    public $timestamps = false;

    protected $fillable = [
        'id_produksi', 'jumlah_stok', 'tanggal_update'
    ];
}