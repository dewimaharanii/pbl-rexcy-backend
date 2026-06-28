<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_hilir', function (Blueprint $table) {

            $table->string('Id_Mitra', 10)->primary();

            $table->string('Nama_Mitra', 100);

            $table->string('Username', 50)->unique();

            $table->string('Kata_Sandi', 255);

            $table->string('No_HP', 20);

            $table->text('Alamat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_hilir');
    }
};