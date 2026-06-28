<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class MitraHilir extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'mitra_hilir';
    protected $primaryKey = 'id_mitra';
    public $timestamps = false;

    protected $fillable = [
        'nama_mitra', 'email', 'password',
        'no_telepon', 'alamat', 'status_verifikasi'
    ];

    protected $hidden = ['password'];
}