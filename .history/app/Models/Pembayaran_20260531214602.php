<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = false;

    protected $fillable = [
        'id_transaksi', 'metode_pembayaran',
        'status_pembayaran', 'tanggal_pembayaran', 'bukti_pembayaran'
    ];
}