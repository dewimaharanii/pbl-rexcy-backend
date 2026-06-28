<?php

namespace App\Http\Controllers;

use App\Models\MitraHilir;
use App\Models\Permintaan;
use App\Models\Transaksi;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MitraHilirController extends Controller
{
    // Register Mitra
public function register(Request $request)
{
    $request->validate([
        'Nama_Mitra' => 'required',
        'Username' => 'required|unique:mitra_hilir,Username',
        'Kata_Sandi' => 'required|min:6',
        'No_HP' => 'required',
        'Alamat' => 'required'
    ]);

    $last = MitraHilir::orderBy('Id_Mitra', 'desc')->first();

    if ($last) {
        $number = (int) substr($last->Id_Mitra, 3) + 1;
    } else {
        $number = 1;
    }

    $idMitra = 'MTR' . str_pad($number, 3, '0', STR_PAD_LEFT);

    $mitra = MitraHilir::create([
        'Id_Mitra' => $idMitra,
        'Nama_Mitra' => $request->Nama_Mitra,
        'Username' => $request->Username,
        'Kata_Sandi' => Hash::make($request->Kata_Sandi),
        'No_HP' => $request->No_HP,
        'Alamat' => $request->Alamat,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'data' => $mitra
    ], 201);
}

    // Login Mitra
    public function login(Request $request)
{
    $request->validate([
        'Username' => 'required',
        'Kata_Sandi' => 'required'
    ]);

    $mitra = MitraHilir::where('Username', $request->Username)->first();

    if (!$mitra) {
        return response()->json([
            'success' => false,
            'message' => 'Username tidak ditemukan'
        ], 401);
    }

    if (!Hash::check($request->Kata_Sandi, $mitra->Kata_Sandi)) {
        return response()->json([
            'success' => false,
            'message' => 'Password salah'
        ], 401);
    }

    $token = $mitra->createToken('mitra-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'token' => $token,
        'user' => $mitra
    ]);
}

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // Lihat semua produk tersedia
    public function getProduk()
    {
        $produk = Produk::all();
        return response()->json(['success' => true, 'data' => $produk]);
    }

    // Buat permintaan produk
    public function buatPermintaan(Request $request)
    {
        $request->validate([
            'id_produksi' => 'required',
            'jumlah_diminta' => 'required|numeric'
        ]);

        $permintaan = Permintaan::create([
            'id_mitra' => $request->user()->id_mitra,
            'id_produksi' => $request->id_produksi,
            'jumlah_diminta' => $request->jumlah_diminta,
            'status' => 'menunggu',
            'tanggal_permintaan' => now(),
            'catatan' => $request->catatan
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan berhasil dibuat',
            'data' => $permintaan
        ], 201);
    }

    // Lihat riwayat permintaan
    public function getPermintaan(Request $request)
    {
        $permintaan = Permintaan::where('id_mitra', $request->user()->id_mitra)->get();
        return response()->json(['success' => true, 'data' => $permintaan]);
    }
}