<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'admin';
    
    // Sesuaikan dengan nama kolom primary key di database-mu
    protected $primaryKey = 'id_admin'; 

    protected $fillable = [
        'Username',
        'Kata_Sandi',
        'Nama_Admin',
    ];

    // Memberitahu Laravel bahwa password ada di kolom 'Kata_Sandi'
    public function getAuthPassword()
    {
        return $this->Kata_Sandi;
    }

    // Pastikan cast juga menggunakan huruf kapital sesuai kolom
    protected $casts = [
        'Kata_Sandi' => 'hashed',
    ];
}