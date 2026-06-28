<?php

namespace App\Http\Controllers;

use App\Models\MitraHilir;
use App\Models\Permintaan;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MitraHilirController extends Controller
{
    // Register Mitra
    public function register(Request $request)
    {
        $request->validate([
            'Nama_Mitra' => 'required',
            'Username'   => 'required|unique:mitra_hilir,Username',
            'Kata_Sandi' => 'required|min:6',
            'No_HP'      => 'required',
            'Alamat'     => 'nullable',
        ]);

        $lastMitra = MitraHilir::orderBy('Id_Mitra', 'desc')->first();
        $newId = $lastMitra 
            ? 'MTR' . str_pad((int)substr($lastMitra->Id_Mitra, 3) + 1, 3, '0', STR_PAD_LEFT) 
            : 'MTR001';

        $mitra = MitraHilir::create([
            'Id_Mitra'   => $newId,
            'Nama_Mitra' => $request->Nama_Mitra,
            'Username'   => $request->Username,
            'Kata_Sandi' => Hash::make($request->Kata_Sandi), 
            'No_HP'      => $request->No_HP,
            'Alamat'     => $request->Alamat ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data'    => $mitra
        ], 201);
    }

    // Login Mitra
    public function login(Request $request)
    {
        $request->validate([
            'Username'   => 'required',
            'Kata_Sandi' => 'required'
        ]);

        $mitra = MitraHilir::where('Username', $request->Username)->first();

        if (!$mitra || !Hash::check($request->Kata_Sandi, $mitra->Kata_Sandi)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        $token = $mitra->createToken('mitra-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $mitra
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // Lihat semua produk
    public function getProduk()
    {
        $produk = Produk::with('produsen')->get();
        return response()->json(['success' => true, 'data' => $produk]);
    }

    // Buat permintaan khusus
    public function buatPermintaan(Request $request)
    {
        $request->validate([
            'Id_Produksi'    => 'required',
            'Jumlah_Diminta' => 'required|numeric',
        ]);

        $mitra = $request->user();

        $lastPermintaan = Permintaan::where('Id_Permintaan', 'like', 'PMT%')
                            ->orderBy('Id_Permintaan', 'desc')
                            ->first();
                            
        if ($lastPermintaan) {
            $lastNumber = (int) substr($lastPermintaan->Id_Permintaan, 3);
            $newId = 'PMT' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newId = 'PMT0001';
        }

        $permintaan = Permintaan::create([
            'Id_Permintaan'      => $newId, 
            'Id_Mitra'           => $mitra->Id_Mitra,
            'Id_Produksi'        => $request->Id_Produksi,
            'Jumlah_Diminta'     => $request->Jumlah_Diminta,
            'Catatan'            => $request->Catatan ?? '',
            'Status'             => 'Pending',
            'Tanggal_Permintaan' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan khusus berhasil dikirim ke produsen',
            'data'    => $permintaan
        ], 201);
    }

    // Lihat riwayat permintaan khusus
    public function getPermintaan(Request $request)
    {
        $mitra = $request->user();

        $permintaan = Permintaan::where('Id_Mitra', $mitra->Id_Mitra)
            ->with('produk')
            ->orderBy('Tanggal_Permintaan', 'desc')
            ->get();

        $mappedData = $permintaan->map(function ($item) {
            $hargaProduk = $item->produk ? $item->produk->Harga_Produksi : 0;
            $totalHarga = $item->Jumlah_Diminta * $hargaProduk;

            return [
                'id_permintaan'      => $item->Id_Permintaan,
                'nama_produk'        => $item->produk ? $item->produk->Nama_Produk : 'Produk Laut',
                'jumlah_permintaan'  => $item->Jumlah_Diminta,
                'status'             => strtolower($item->Status), 
                'tanggal_permintaan' => $item->Tanggal_Permintaan,
                'estimasi_total'     => $totalHarga,
                'total_harga'        => $totalHarga, 
            ];
        });

        return response()->json(['success' => true, 'data' => $mappedData]);
    }

    // Konfirmasi Pesanan Selesai
    public function konfirmasiSelesai(Request $request, $id)
    {
        if (str_starts_with($id, 'PMT')) {
            $pesanan = Permintaan::where('Id_Permintaan', $id)->first();
        } else {
            $pesanan = Transaksi::where('Id_Transaksi', $id)->orWhere('id_transaksi', $id)->first();
        }
        
        if (!$pesanan) return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);

        $pesanan->Status = 'Selesai'; 
        $pesanan->save();

        return response()->json(['success' => true, 'message' => 'Pesanan berhasil diselesaikan']);
    }
    
    // Update Profile
    public function updateProfile(Request $request)
    {
        $mitra = $request->user();
        $request->validate([
            'Nama_Mitra' => 'required',
            'No_HP'      => 'nullable',
            'Kata_Sandi_Baru' => 'nullable|min:6',
        ]);

        $mitra->Nama_Mitra = $request->Nama_Mitra;
        if ($request->filled('No_HP')) $mitra->No_HP = $request->No_HP;
        if ($request->filled('Kata_Sandi_Baru')) $mitra->Kata_Sandi = Hash::make($request->Kata_Sandi_Baru); 

        $mitra->save();
        return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui', 'user' => $mitra]);
    }

    public function getPesananMitraAll(Request $request)
    {
        $mitra = $request->user();

        // Ambil Pembelian (TRX)
        $trx = Transaksi::where('Id_Mitra', $mitra->Id_Mitra)
                        ->with('produk')->get();
        
        // Ambil Permintaan (PMT)
        $pmt = Permintaan::where('Id_Mitra', $mitra->Id_Mitra)
                        ->with('produk')->get();

        // Gabungkan
        $data = $trx->map(fn($i) => [
            'id' => $i->Id_Transaksi, 
            'jenis' => 'pembelian', 
            'status' => strtolower($i->Status), 
            'total' => $i->Total_Harga, 
            'tanggal' => $i->Tanggal_Transaksi, 
            'produk' => $i->produk->Nama_Produk ?? 'Produk'
        ])->concat($pmt->map(fn($i) => [
            'id' => $i->Id_Permintaan, 
            'jenis' => 'permintaan', 
            'status' => strtolower($i->Status), 
            'total' => ($i->Jumlah_Diminta * ($i->produk->Harga_Produksi ?? 0)), 
            'tanggal' => $i->Tanggal_Permintaan, 
            'produk' => $i->produk->Nama_Produk ?? 'Produk'
        ]))->sortByDesc('tanggal')->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

   
    public function bayarPermintaan(Request $request, $id)
    {
        try {
            $pesanan = \App\Models\Permintaan::with(['mitra', 'produk'])->where('Id_Permintaan', $id)->first();
            
            if (!$pesanan) {
                return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
            }

            $imagePath = null;
            if ($request->hasFile('bukti_transfer')) {
                $imagePath = $request->file('bukti_transfer')->store('bukti_pembayaran', 'public');
            } else {
                // 🚀 Jika foto gagal masuk, kasih error!
                return response()->json(['success' => false, 'message' => 'GAGAL: Bukti transfer wajib diunggah!']);
            }

            // 🚀 MASUKKAN KE TABEL PEMBAYARAN (Ini yang membuat PMT muncul di Admin)
            \Illuminate\Support\Facades\DB::table('pembayaran')->insert([
                'id_transaksi'      => $id,
                'jenis'             => 'permintaan', // Pastikan namanya "permintaan"
                'nama_mitra'        => $pesanan->mitra->Nama_Mitra ?? 'Mitra Hilir',
                'nama_produk'       => $pesanan->produk->Nama_Produk ?? 'Produk Laut',
                'jumlah_bayar'      => $pesanan->Jumlah_Diminta * ($pesanan->produk->Harga_Produksi ?? 0),
                'bukti_transfer'    => $imagePath,
                'tanggal_bayar'     => now(),
                'status_pembayaran' => 'Menunggu Konfirmasi',
            ]);

            // Ubah Status di Tabel Permintaan
            $pesanan->Status = 'Menunggu Validasi';
            $pesanan->save();

            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dikirim dan menunggu validasi Admin']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error Backend: ' . $e->getMessage()]);
        }
    }
}