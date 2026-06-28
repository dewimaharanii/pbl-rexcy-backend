<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Produk;

class Produsen extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'produsen';
    protected $primaryKey = 'Id_Produsen';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Id_Produsen',
        'Id_Pendaftaran',
        'Nama_Produsen',
        'Username',
        'Kata_Sandi',
        'No_HP',
        'Alamat',
        'Jenis_Usaha'
    ];

    protected $hidden = [
        'Kata_Sandi'
    ];

    // FIX: Daftarkan relasi ke tabel Produk (produksi)
    public function produk()
    {
        return $this->hasMany(Produk::class, 'Id_Produsen', 'Id_Produsen');
    }
}