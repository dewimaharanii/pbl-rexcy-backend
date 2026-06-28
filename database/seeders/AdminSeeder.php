<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admin')->updateOrInsert(
            ['Username' => 'admin'],
            [
                'Nama_Admin' => 'Administrator',
                'Username'   => 'admin',
                'Kata_Sandi' => Hash::make('admin123'),
            ]
        );
    }
}