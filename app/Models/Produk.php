<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Produsen;

class Produk extends Model
{
    protected $table = 'produksi';
    protected $primaryKey = 'Id_Produksi';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Id_Produksi', 'Id_Produsen', 'Nama_Produk', 'Jumlah_Stok',
        'Harga_Produksi', 'Lokasi_Tangkap', 'Catatan', 'Dibuat_Pada', 'Gambar'
    ];

    // Relasi ke Produsen
    public function produsen()
    {
        return $this->belongsTo(Produsen::class, 'Id_Produsen', 'Id_Produsen');
    }

    // Akses gambar untuk Flutter
    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute()
    {
        if ($this->Gambar) {
            return url('storage/' . $this->Gambar);
        }
        return null;
    }
}