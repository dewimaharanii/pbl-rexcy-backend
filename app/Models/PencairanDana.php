<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PencairanDana extends Model
{
    protected $table = 'pencairan_dana';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_pencairan',
        'id_produsen',
        'jumlah_dana',
        'nama_bank',
        'no_rekening',
        'nama_pemilik_rekening',
        'status',
        'keterangan_admin',
        'tanggal_pengajuan',
        'tanggal_diproses',
    ];

    public function produsen()
    {
        return $this->belongsTo(Produsen::class, 'id_produsen', 'Id_Produsen');
    }
}