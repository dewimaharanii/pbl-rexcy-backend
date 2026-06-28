<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransaksiController extends Controller
{
    // Buat transaksi baru
    public function buatTransaksi(Request $request)
    {
        $request->validate([
            'id_produksi' => 'required',
            'jumlah'      => 'required|numeric',
            'total_harga' => 'required|numeric',
        ]);

        $mitra = $request->user();

        // Generate Id_Transaksi otomatis
        $lastTrx = Transaksi::orderBy('Id_Transaksi', 'desc')->first();
        if ($lastTrx) {
            $lastNumber = (int) substr($lastTrx->Id_Transaksi, 3);
            $newId = 'TRX' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newId = 'TRX0001';
        }

        $transaksi = Transaksi::create([
            'Id_Transaksi'      => $newId,
            'Id_Mitra'          => $mitra->Id_Mitra,
            'Id_Produksi'       => $request->id_produksi,
            'Jumlah'            => $request->jumlah,
            'Total_Harga'       => $request->total_harga,
            'Status'            => 'menunggu',
            'Tanggal_Transaksi' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat',
            'data'    => $transaksi,
        ], 201);
    }

    // Lihat semua transaksi milik mitra yang login
    public function getTransaksi(Request $request)
    {
        $mitra = $request->user();

        $transaksi = Transaksi::where('Id_Mitra', $mitra->Id_Mitra)
            ->orderBy('Tanggal_Transaksi', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $transaksi,
        ]);
    }

    // Update status transaksi
    public function updateStatus(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        $transaksi->Status = $request->status;
        $transaksi->save();

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi diupdate',
            'data'    => $transaksi,
        ]);
    }

    // Upload bukti pembayaran
    public function uploadPembayaran(Request $request)
    {
        $request->validate([
            'id_transaksi'      => 'required',
            'metode_pembayaran' => 'required',
            'bukti_pembayaran'  => 'required|image|max:2048',
        ]);

        $file     = $request->file('bukti_pembayaran');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/pembayaran'), $filename);

        $pembayaran = Pembayaran::create([
            'Id_Transaksi'      => $request->id_transaksi,
            'Metode_Pembayaran' => $request->metode_pembayaran,
            'Status_Pembayaran' => 'menunggu',
            'Tanggal_Pembayaran'=> now(),
            'Bukti_Pembayaran'  => $filename,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diupload',
            'data'    => $pembayaran,
        ]);
    }
}