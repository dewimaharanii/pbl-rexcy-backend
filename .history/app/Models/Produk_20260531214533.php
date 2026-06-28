<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produksi';
    protected $primaryKey = 'id_produksi';
    public $timestamps = false;

    protected $fillable = [
        'id_produsen', 'nama_produk', 'deskripsi',
        'harga', 'stok', 'satuan', 'foto_produk'
    ];
}