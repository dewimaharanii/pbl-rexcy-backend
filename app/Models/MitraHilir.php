<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class MitraHilir extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'mitra_hilir';

    protected $primaryKey = 'Id_Mitra';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

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

    public function getAuthPassword()
    {
        return $this->Kata_Sandi;
    }

    protected $casts = [
        'Kata_Sandi' => 'hashed',
    ];
}