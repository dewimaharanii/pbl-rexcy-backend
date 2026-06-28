<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table      = 'transaksi';
    protected $primaryKey = 'Id_Transaksi';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'Id_Transaksi',
        'Id_Mitra',
        'Id_Produksi',
        'Jumlah',
        'Total_Harga',
        'Status',
        'Tanggal_Transaksi',
    ];
}