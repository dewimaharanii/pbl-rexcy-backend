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

            // Simpan gambar jika ada (opsional)
            $imagePath = null;
            if ($request->hasFile('bukti_transfer')) {
                $imagePath = $request->file('bukti_transfer')->store('bukti_pembayaran', 'public');
            }

            // Generate ID Transaksi
            $lastTrx = Transaksi::orderBy('Id_Transaksi', 'desc')->first();
            $newId = $lastTrx
                ? 'TRX' . str_pad((int) substr($lastTrx->Id_Transaksi, 3) + 1, 3, '0', STR_PAD_LEFT)
                : 'TRX001';

            // Ambil nilai field — support lowercase maupun mixed case dari Flutter
            $idProduksi = $request->input('id_produksi') ?? $request->input('Id_Produksi');
            $jumlah     = $request->input('jumlah')      ?? $request->input('Jumlah');
            $totalHarga = $request->input('total_harga') ?? $request->input('Total_Harga') ?? 0;
            $catatan    = $request->input('catatan')     ?? $request->input('Catatan', '');
            $namaPemesan = $request->input('nama_pemesan') ?? $request->input('Nama_Pemesan', '');
            $noTelp     = $request->input('no_telp')     ?? $request->input('No_Telp', '');
            $alamat     = $request->input('alamat')      ?? $request->input('Alamat_Pemesan', $request->input('Alamat', ''));

            // Buat Transaksi
            Transaksi::create([
                'Id_Transaksi'      => $newId,
                'Id_Mitra'          => $user->Id_Mitra,
                'Id_Produksi'       => $idProduksi,
                'Jumlah'            => $jumlah,
                'Total_Harga'       => $totalHarga,
                'Status'            => 'Menunggu Validasi',
                'Tanggal_Transaksi' => now(),
                'catatan'           => $catatan,
                'nama_pemesan'      => $namaPemesan,
                'no_telp'           => $noTelp,
                'alamat_pemesan'    => $alamat,
            ]);

            // Masukkan ke Tabel Pembayaran untuk Admin
            $produk = Produk::find($idProduksi);
            DB::table('pembayaran')->insert([
                'id_transaksi'      => $newId,
                'jenis'             => 'pembelian',
                'nama_mitra'        => $user->Nama_Mitra ?? 'Mitra Hilir',
                'nama_produk'       => $produk ? $produk->Nama_Produk : 'Produk Laut',
                'jumlah_bayar'      => $totalHarga,
                'bukti_transfer'    => $imagePath,
                'tanggal_bayar'     => now(),
                'status_pembayaran' => 'Menunggu Konfirmasi',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil, menunggu validasi Admin',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Ambil Riwayat Transaksi Sisi Mitra
    public function getTransaksiMitra(Request $request)
    {
        $mitra = $request->user();

        $transaksi = Transaksi::where('Id_Mitra', $mitra->Id_Mitra)
            ->with('produk')
            ->orderBy('Id_Transaksi', 'desc') // FIX: pakai primary key yang benar
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
                'jenis_pesanan'      => 'pembelian',
                'nama_pemesan'       => $item->nama_pemesan ?? '',
                'no_telp'            => $item->no_telp ?? '',
                'alamat_pemesan'     => $item->alamat_pemesan ?? '',
                'catatan'            => $item->catatan ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $mappedData,
        ]);
    }
}