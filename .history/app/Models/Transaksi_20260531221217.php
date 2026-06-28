<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'Id_Transaksi';
    public $timestamps = false;

    protected $fillable = [
        'Id_Permintaan', 'Id_Produsen', 'Id_Mitra',
        'Tanggal_Transaksi', 'Total_Harga',
        'Status_Transaksi', 'Status_Konfirmasi'
    ];
}