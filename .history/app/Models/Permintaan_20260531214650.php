<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    protected $table = 'permintaan';
    protected $primaryKey = 'id_permintaan';
    public $timestamps = false;

    protected $fillable = [
        'id_mitra', 'id_produksi', 'jumlah_diminta',
        'status', 'tanggal_permintaan', 'catatan'
    ];
}