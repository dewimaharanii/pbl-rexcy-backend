<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Tambah catatan jika belum ada
            if (!Schema::hasColumn('transaksi', 'catatan')) {
                $table->text('catatan')->nullable()->after('Total_Harga');
            }
            if (!Schema::hasColumn('transaksi', 'nama_pemesan')) {
                $table->string('nama_pemesan', 100)->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('transaksi', 'no_telp')) {
                $table->string('no_telp', 20)->nullable()->after('nama_pemesan');
            }
            if (!Schema::hasColumn('transaksi', 'alamat_pemesan')) {
                $table->text('alamat_pemesan')->nullable()->after('no_telp');
            }
        });

        Schema::table('permintaan', function (Blueprint $table) {
            if (!Schema::hasColumn('permintaan', 'nama_pemesan')) {
                $table->string('nama_pemesan', 100)->nullable()->after('Catatan');
            }
            if (!Schema::hasColumn('permintaan', 'no_telp')) {
                $table->string('no_telp', 20)->nullable()->after('nama_pemesan');
            }
            if (!Schema::hasColumn('permintaan', 'alamat_pemesan')) {
                $table->text('alamat_pemesan')->nullable()->after('no_telp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['nama_pemesan', 'no_telp', 'alamat_pemesan']);
        });
        Schema::table('permintaan', function (Blueprint $table) {
            $table->dropColumn(['nama_pemesan', 'no_telp', 'alamat_pemesan']);
        });
    }
};
