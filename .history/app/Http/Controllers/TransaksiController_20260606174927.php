<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    // Buat transaksi baru
    public function buatTransaksi(Request $request)
{
    $request->validate([
        'id_produksi'  => 'required',
        'jumlah'       => 'required|numeric',
        'total_harga'  => 'required|numeric',
    ]);

    // Ambil Id_Mitra dari user yang login
    $mitra = $request->user();

    $transaksi = Transaksi::create([
        'Id_Mitra'           => $mitra->Id_Mitra,
        'Id_Produksi'        => $request->id_produksi,
        'Jumlah'             => $request->jumlah,
        'Total_Harga'        => $request->total_harga,
        'Status'             => 'menunggu',
        'Tanggal_Transaksi'  => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil dibuat',
        'data'    => $transaksi,
    ], 201);
}

public function getTransaksi(Request $request)
{
    $mitra     = $request->user();
    $transaksi = Transaksi::where('Id_Mitra', $mitra->Id_Mitra)
                    ->orderBy('Tanggal_Transaksi', 'desc')
                    ->get();

    return response()->json([
        'success' => true,
        'data'    => $transaksi,
    ]);
}