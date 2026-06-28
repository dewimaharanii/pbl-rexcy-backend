<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Produsen extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'produsen';
    protected $primaryKey = 'Id_Produsen';

    // Karena ID menggunakan format PRD001, PRD002, dst
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
}