<?php
 
namespace App\Http\Controllers;
 
use App\Models\Produsen;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
 
class ProdusenController extends Controller
{
    // Register Produsen
    public function register(Request $request)
    {
        $request->validate([
            'Nama_Produsen' => 'required',
            'Username'      => 'required|unique:produsen,Username',
            'Kata_Sandi'    => 'required|min:6',
            'No_HP'         => 'required',
            'Alamat'        => 'required',
            'Jenis_Usaha'   => 'required'
        ]);
 
        $total       = Produsen::count() + 1;
        $id_produsen = 'PRD' . str_pad($total, 3, '0', STR_PAD_LEFT);
 
        $produsen = Produsen::create([
            'Id_Produsen'   => $id_produsen,
            'Nama_Produsen' => $request->Nama_Produsen,
            'Username'      => $request->Username,
            'Kata_Sandi'    => Hash::make($request->Kata_Sandi),
            'No_HP'         => $request->No_HP,
            'Alamat'        => $request->Alamat,
            'Jenis_Usaha'   => $request->Jenis_Usaha
        ]);
 
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data'    => $produsen
        ], 201);
    }
 
    // Login Produsen
    public function login(Request $request)
    {
        $request->validate([
            'Username'   => 'required',
            'Kata_Sandi' => 'required'
        ]);
 
        $produsen = Produsen::where('Username', $request->Username)->first();
 
        if (!$produsen || !Hash::check($request->Kata_Sandi, $produsen->Kata_Sandi)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }
 
        $token = $produsen->createToken('produsen-token')->plainTextToken;
 
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $produsen
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
            'Nama_Produk'    => 'required',
            'Jumlah_Stok'    => 'required|numeric',
            'Harga_Produksi' => 'required|numeric',
        ]);
 
        $produk = Produk::create([
            'Id_Produsen'    => $request->user()->Id_Produsen,
            'Nama_Produk'    => $request->Nama_Produk,
            'Jumlah_Stok'    => $request->Jumlah_Stok,
            'Harga_Produksi' => $request->Harga_Produksi,
            'Lokasi_Tangkap' => $request->Lokasi_Tangkap,
            'Catatan'        => $request->Catatan,
            'Dibuat_Pada'    => now()
        ]);
 
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $produk
        ], 201);
    }
 
    // List Produk milik produsen
    public function getProduk(Request $request)
    {
        $produk = Produk::where('Id_Produsen', $request->user()->Id_Produsen)->get();
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