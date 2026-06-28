<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProdusenController;
use App\Http\Controllers\MitraHilirController;
use App\Http\Controllers\TransaksiController;

// =====================
// AUTH ROUTES (Public)
// =====================

// Admin Login
Route::post('/admin/login', [AdminController::class, 'login']);

// Produsen
Route::post('/produsen/register', [ProdusenController::class, 'register']);
Route::post('/produsen/login', [ProdusenController::class, 'login']);

// Mitra Hilir
Route::post('/mitra/register', [MitraHilirController::class, 'register']);
Route::post('/mitra/login', [MitraHilirController::class, 'login']);


// =====================
// ADMIN ROUTES (Protected)
// =====================
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/produsen', [AdminController::class, 'getProdusen']);
    Route::put('/produsen/verifikasi/{id}', [AdminController::class, 'verifikasiProdusen']);
    Route::get('/mitra', [AdminController::class, 'getMitra']);
    Route::put('/mitra/verifikasi/{id}', [AdminController::class, 'verifikasiMitra']);
});


// =====================
// PRODUSEN ROUTES (Protected)
// =====================
Route::middleware('auth:sanctum')->prefix('produsen')->group(function () {
    Route::post('/logout', [ProdusenController::class, 'logout']);
    Route::get('/produk', [ProdusenController::class, 'getProduk']);
    Route::post('/produk', [ProdusenController::class, 'tambahProduk']);
    Route::put('/produk/{id}', [ProdusenController::class, 'updateProduk']);
    Route::delete('/produk/{id}', [ProdusenController::class, 'hapusProduk']);
});


// =====================
// MITRA HILIR ROUTES (Protected)
// =====================
Route::middleware('auth:sanctum')->prefix('mitra')->group(function () {
    Route::post('/logout', [MitraHilirController::class, 'logout']);
    Route::get('/produk', [MitraHilirController::class, 'getProduk']);
    Route::post('/permintaan', [MitraHilirController::class, 'buatPermintaan']);
    Route::get('/permintaan', [MitraHilirController::class, 'getPermintaan']);
    Route::post('/transaksi', [TransaksiController::class, 'buatTransaksi']);
    Route::get('/transaksi', [TransaksiController::class, 'getTransaksi']);
    Route::post('/transaksi/pembayaran', [TransaksiController::class, 'uploadPembayaran']);
    Route::put('/profile', [MitraHilirController::class, 'updateProfile']);
});