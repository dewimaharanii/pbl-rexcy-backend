<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MitraHilirController;
use App\Http\Controllers\ProdusenController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\AdminController; 

// ==========================================
// RUTE MITRA HILIR (PEMBELI)
// ==========================================
Route::prefix('mitra')->group(function () {
    Route::post('/register', [MitraHilirController::class, 'register']);
    Route::post('/login', [MitraHilirController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MitraHilirController::class, 'logout']);
        Route::get('/produk', [MitraHilirController::class, 'getProduk']);
        Route::put('/profile', [MitraHilirController::class, 'updateProfile']);

        //Permintaan Khusus (PMT)
        Route::post('/permintaan', [MitraHilirController::class, 'buatPermintaan']);
        Route::get('/permintaan', [MitraHilirController::class, 'getPermintaan']);
        Route::post('/permintaan/{id}/selesai', [MitraHilirController::class, 'konfirmasiSelesai']); 

        //Pembelian Langsung (TRX) 
        Route::post('/transaksi', [TransaksiController::class, 'buatTransaksi']);
        Route::get('/transaksi', [TransaksiController::class, 'getTransaksiMitra']);

        //Transaksi & Pembayaran
        Route::get('/pesanan-all', [MitraHilirController::class, 'getPesananMitraAll']);
        Route::post('/pembayaran/{id}', [MitraHilirController::class, 'bayarPermintaan']);
    });
});

// ==========================================
//RUTE PRODUSEN (PENJUAL)
// ==========================================
Route::prefix('produsen')->group(function () {
    Route::post('/register', [ProdusenController::class, 'register']);
    Route::post('/login', [ProdusenController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ProdusenController::class, 'logout']);

        //Kelola Produk
        Route::post('/produk', [ProdusenController::class, 'tambahProduk']);
        Route::get('/produk', [ProdusenController::class, 'getProduk']);
        Route::put('/produk/{id}', [ProdusenController::class, 'updateProduk']);
        Route::delete('/produk/{id}', [ProdusenController::class, 'hapusProduk']);

        //Tab Permintaan Masuk (PMT)
        Route::get('/permintaan', [ProdusenController::class, 'getPermintaanMasuk']);
        Route::post('/permintaan/{id}/proses', [ProdusenController::class, 'prosesPermintaan']);
        Route::post('/permintaan/{id}/tolak', [ProdusenController::class, 'tolakPermintaan']);
        Route::post('/permintaan/{id}/terima', [ProdusenController::class, 'terimaPermintaan']);

        //Tab Pembelian Masuk (TRX)
        Route::get('/pesanan-masuk-aktif', [ProdusenController::class, 'getRiwayatTransaksi']);
        Route::post('/transaksi/{id}/proses', [ProdusenController::class, 'prosesTransaksi']);
        Route::post('/transaksi/{id}/tolak', [ProdusenController::class, 'tolakTransaksi']);

        //Tab Riwayat Gabungan Produsen
        Route::get('/riwayat-all', [ProdusenController::class, 'getRiwayatTotalSelesai']);
        Route::post('/permintaan/{id}/bayar', [MitraHilirController::class, 'bayarPermintaan']);
    });
});

// ==========================================
// RUTE ADMIN
// ==========================================
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        //Kelola Pembayaran
        Route::get('/pembayaran-pending', [AdminController::class, 'getPembayaran']);
        Route::post('/pembayaran/validasi/{jenis}/{id}', [AdminController::class, 'konfirmasiPembayaran']);
        
        //Kelola Produsen dkk...
        Route::get('/produsen', [AdminController::class, 'getProdusen']);
        Route::post('/produsen', [AdminController::class, 'tambahProdusen']);
        Route::put('/produsen/{id}', [AdminController::class, 'updateProdusen']);
        Route::delete('/produsen/{id}', [AdminController::class, 'hapusProdusen']);
        Route::get('/mitra', [AdminController::class, 'getMitra']);
        Route::delete('/mitra/{id}', [AdminController::class, 'hapusMitra']);
        Route::get('/produksi', [AdminController::class, 'getProduksi']);
        Route::get('/transaksi', [AdminController::class, 'getTransaksi']);
    });
});

// 🚀 RUTE PUBLIK: BYPASS CORS UNTUK GAMBAR (WAJIB DI LUAR MIDDLEWARE)
Route::get('/file/bukti-transfer/{filename}', [\App\Http\Controllers\FileController::class, 'show']);