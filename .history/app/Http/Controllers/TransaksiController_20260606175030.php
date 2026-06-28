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

    // Lihat semua transaksi
    public function getTransaksi(Request $request)
    {
        $transaksi = Transaksi::where('id_mitra', $request->user()->id_mitra)->get();
        return response()->json(['success' => true, 'data' => $transaksi]);
    }

    // Update status transaksi
    public function updateStatus(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);
        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
        }
        $transaksi->status = $request->status;
        $transaksi->save();
        return response()->json(['success' => true, 'message' => 'Status transaksi diupdate', 'data' => $transaksi]);
    }

    // Upload bukti pembayaran
    public function uploadPembayaran(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required',
            'metode_pembayaran' => 'required',
            'bukti_pembayaran' => 'required|image|max:2048'
        ]);

        $file = $request->file('bukti_pembayaran');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/pembayaran'), $filename);

        $pembayaran = Pembayaran::create([
            'id_transaksi' => $request->id_transaksi,
            'metode_pembayaran' => $request->metode_pembayaran,
            'status_pembayaran' => 'menunggu',
            'tanggal_pembayaran' => now(),
            'bukti_pembayaran' => $filename
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diupload',
            'data' => $pembayaran
        ]);
    }
}