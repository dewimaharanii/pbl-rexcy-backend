<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_mitra', 'id_produksi', 'jumlah',
        'total_harga', 'status', 'tanggal_transaksi'
    ];
}