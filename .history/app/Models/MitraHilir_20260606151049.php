<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class MitraHilir extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'mitra_hilir';
    protected $primaryKey = 'Id_Mitra';

    public $timestamps = false;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'Id_Mitra',
        'Nama_Mitra',
        'Username',
        'Kata_Sandi',
        'No_HP',
        'Alamat'
    ];

    protected $hidden = [
        'Kata_Sandi'
    ];
}