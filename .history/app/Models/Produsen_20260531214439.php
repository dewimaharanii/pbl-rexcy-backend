<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Produsen extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'produsen';
    protected $primaryKey = 'id_produsen';
    public $timestamps = false;

    protected $fillable = [
        'nama_produsen', 'email', 'password',
        'no_telepon', 'alamat', 'status_verifikasi'
    ];

    protected $hidden = ['password'];
}