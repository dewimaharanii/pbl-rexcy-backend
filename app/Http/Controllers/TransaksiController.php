<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // Dipanggil saat Mitra melakukan Pembelian Langsung (TRX)
    public function buatTransaksi(Request $request)
    {
        try {
            $user = $request->user();

            // 🚀 JEBAKAN BATMAN: Cek apakah foto benar-benar masuk
            if (!$request->hasFile('bukti_transfer')) {
                return response()->json(['success' => false, 'message' => 'GAGAL: Foto bukti transfer tidak terbaca oleh server Laravel!']);
            }

            // Simpan gambar
            $imagePath = $request->file('bukti_transfer')->store('bukti_pembayaran', 'public');

            // Generate ID Transaksi
            $lastTrx = \App\Models\Transaksi::orderBy('Id_Transaksi', 'desc')->first();
            $newId = $lastTrx ? 'TRX' . str_pad((int)substr($lastTrx->Id_Transaksi, 3) + 1, 3, '0', STR_PAD_LEFT) : 'TRX001';

            // Buat Transaksi Induk (Status WAJIB: Menunggu Validasi)
            \App\Models\Transaksi::create([
                'Id_Transaksi'      => $newId,
                'Id_Mitra'          => $user->Id_Mitra,
                'Id_Produksi'       => $request->id_produksi,
                'Jumlah'            => $request->jumlah,
                'Total_Harga'       => $request->total_harga,
                'Status'            => 'Menunggu Validasi', // 🚀 Produsen akan melihat ini
                'Tanggal_Transaksi' => now(),
            ]);

            // Masukkan ke Tabel Pembayaran untuk Admin
            $produk = \App\Models\Produk::find($request->id_produksi);
            DB::table('pembayaran')->insert([
                'id_transaksi'      => $newId,
                'jenis'             => 'pembelian',
                'nama_mitra'        => $user->Nama_Mitra ?? 'Mitra Hilir',
                'nama_produk'       => $produk->Nama_Produk ?? 'Produk Laut',
                'jumlah_bayar'      => $request->total_harga,
                'bukti_transfer'    => $imagePath, // 🚀 FOTO TERSIMPAN AMAN
                'tanggal_bayar'     => now(),
                'status_pembayaran' => 'Menunggu Konfirmasi',
            ]);

            return response()->json(['success' => true, 'message' => 'Pembelian berhasil, menunggu validasi Admin']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Ambil Riwayat Transaksi Sisi Mitra
    public function getTransaksiMitra(Request $request)
    {
        $mitra = $request->user();

        $transaksi = Transaksi::where('Id_Mitra', $mitra->Id_Mitra)
                              ->with('produk')
                              ->orderBy('id', 'desc')
                              ->get();

        $mappedData = $transaksi->map(function ($item) {
            return [
                'Id_Transaksi'       => $item->Id_Transaksi,
                'id_permintaan'      => $item->Id_Transaksi, 
                'nama_produk'        => $item->produk ? $item->produk->Nama_Produk : 'Produk Laut',
                'jumlah_permintaan'  => $item->Jumlah,
                'status'             => strtolower($item->Status),
                'tanggal_permintaan' => $item->Tanggal_Transaksi,
                'total_harga'        => $item->Total_Harga,
                'jenis_pesanan'      => 'pembelian'
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $mappedData
        ]);
    }
}