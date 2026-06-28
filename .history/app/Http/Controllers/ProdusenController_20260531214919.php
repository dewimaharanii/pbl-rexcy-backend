<?php

namespace App\Http\Controllers;

use App\Models\Produsen;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProdusenController extends Controller
{
    // Register Produsen
    public function register(Request $request)
    {
        $request->validate([
            'nama_produsen' => 'required',
            'email' => 'required|email|unique:produsen,email',
            'password' => 'required|min:6',
            'no_telepon' => 'required',
            'alamat' => 'required'
        ]);

        $produsen = Produsen::create([
            'nama_produsen' => $request->nama_produsen,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
            'status_verifikasi' => 'menunggu'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil, menunggu verifikasi admin',
            'data' => $produsen
        ], 201);
    }

    // Login Produsen
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $produsen = Produsen::where('email', $request->email)->first();

        if (!$produsen || !Hash::check($request->password, $produsen->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        if ($produsen->status_verifikasi !== 'terverifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi admin'
            ], 403);
        }

        $token = $produsen->createToken('produsen-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $produsen
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // Tambah Produk
    public function tambahProduk(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'harga' => 'required|numeric',
            'stok' => 'required|numeric',
            'satuan' => 'required'
        ]);

        $produk = Produk::create([
            'id_produsen' => $request->user()->id_produsen,
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk
        ], 201);
    }

    // List Produk milik produsen
    public function getProduk(Request $request)
    {
        $produk = Produk::where('id_produsen', $request->user()->id_produsen)->get();
        return response()->json(['success' => true, 'data' => $produk]);
    }

    // Update Produk
    public function updateProduk(Request $request, $id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }
        $produk->update($request->all());
        return response()->json(['success' => true, 'message' => 'Produk berhasil diupdate', 'data' => $produk]);
    }

    // Hapus Produk
    public function hapusProduk($id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }
        $produk->delete();
        return response()->json(['success' => true, 'message' => 'Produk berhasil dihapus']);
    }
}