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
            'nama_mitra' => 'required',
            'email' => 'required|email|unique:mitra_hilir,email',
            'password' => 'required|min:6',
            'no_telepon' => 'required',
            'alamat' => 'required'
        ]);

        $mitra = MitraHilir::create([
            'nama_mitra' => $request->nama_mitra,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
            'status_verifikasi' => 'menunggu'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil, menunggu verifikasi admin',
            'data' => $mitra
        ], 201);
    }

    // Login Mitra
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $mitra = MitraHilir::where('email', $request->email)->first();

        if (!$mitra || !Hash::check($request->password, $mitra->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        if ($mitra->status_verifikasi !== 'terverifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi admin'
            ], 403);
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
   <?php

namespace App\Http\Controllers;

use App\Models\MitraHilir;
use App\Models\Permintaan;
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

        $mitra = MitraHilir::create([
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

    // Lihat semua produk
    public function getProduk()
    {
        $produk = Produk::all();
        return response()->json(['success' => true, 'data' => $produk]);
    }

    // Buat permintaan
    public function buatPermintaan(Request $request)
    {
        $request->validate([
            'Id_Produksi' => 'required',
            'Jumlah_Diminta' => 'required|numeric'
        ]);

        $permintaan = Permintaan::create([
            'Id_Mitra' => $request->user()->Id_Mitra,
            'Id_Produksi' => $request->Id_Produksi,
            'Jumlah_Diminta' => $request->Jumlah_Diminta,
            'Status' => 'Menunggu',
            'Tanggal_Permintaan' => now(),
            'Catatan' => $request->Catatan
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
        $permintaan = Permintaan::where('Id_Mitra', $request->user()->Id_Mitra)->get();
        return response()->json(['success' => true, 'data' => $permintaan]);
    }
}         'id_produksi' => $request->id_produksi,
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