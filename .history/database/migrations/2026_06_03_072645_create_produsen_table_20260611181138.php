<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produsen', function (Blueprint $table) {
            $table->id();
            $table->string('Nama_Produsen');
            $table->string('Username')->unique();
            $table->string('Kata_Sandi');
            $table->string('No_HP')->nullable();
            $table->string('Alamat')->nullable();
            $table->string('Jenis_Usaha')->nullable();
            $table->enum('Status_Verifikasi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produsen');
    }
};