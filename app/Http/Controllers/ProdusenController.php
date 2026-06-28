<?php

namespace App\Http\Controllers;

use App\Models\Produsen;
use App\Models\Produk;
use App\Models\Permintaan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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



        return response()->json(['success' => true, 'message' => 'Registrasi berhasil', 'data' => $produsen], 201);

    }



    // Login Produsen

    public function login(Request $request)

    {

        $request->validate(['Username' => 'required', 'Kata_Sandi' => 'required']);

        $produsen = Produsen::where('Username', $request->Username)->first();



        if (!$produsen || !Hash::check($request->Kata_Sandi, $produsen->Kata_Sandi)) {

            return response()->json(['success' => false, 'message' => 'Username atau password salah'], 401);

        }



        return response()->json([

            'success' => true,

            'message' => 'Login berhasil',

            'token'   => $produsen->createToken('produsen-token')->plainTextToken,

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

        $request->validate(['Nama_Produk' => 'required', 'Jumlah_Stok' => 'required|numeric', 'Harga_Produksi' => 'required|numeric']);



        $last  = Produk::orderBy('Id_Produksi', 'desc')->first();

        $newId = $last ? 'PRD' . str_pad(((int) substr($last->Id_Produksi, 3)) + 1, 3, '0', STR_PAD_LEFT) : 'PRD001';



        $gambarPath = $request->hasFile('Gambar') ? $request->file('Gambar')->store('produk', 'public') : null;



        $produk = Produk::create([

            'Id_Produksi'    => $newId,

            'Id_Produsen'    => $request->user()->Id_Produsen,

            'Nama_Produk'    => $request->Nama_Produk,

            'Jumlah_Stok'    => $request->Jumlah_Stok,

            'Harga_Produksi' => $request->Harga_Produksi,

            'Lokasi_Tangkap' => $request->Lokasi_Tangkap ?? '',

            'Catatan'        => $request->Catatan ?? '',

            'Gambar'         => $gambarPath,

            'Dibuat_Pada'    => now(),

        ]);



        return response()->json(['success' => true, 'message' => 'Produk berhasil ditambahkan', 'data' => $produk], 201);

    }



    public function getProduk(Request $request)

    {

        return response()->json(['success' => true, 'data' => $request->user()->produk]);

    }



    public function updateProduk(Request $request, $id)

    {

        $produk = Produk::find($id);

        if (!$produk) return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);



        if ($request->hasFile('Gambar')) {

            if ($produk->Gambar && Storage::disk('public')->exists($produk->Gambar)) Storage::disk('public')->delete($produk->Gambar);

            $produk->Gambar = $request->file('Gambar')->store('produk', 'public');

        }



        if ($request->has('Nama_Produk'))    $produk->Nama_Produk    = $request->Nama_Produk;

        if ($request->has('Jumlah_Stok'))    $produk->Jumlah_Stok    = $request->Jumlah_Stok;

        if ($request->has('Harga_Produksi')) $produk->Harga_Produksi = $request->Harga_Produksi;



        $produk->save();

        return response()->json(['success' => true, 'message' => 'Produk berhasil diupdate', 'data' => $produk]);

    }



    public function hapusProduk($id)

    {

        $produk = Produk::find($id);

        if (!$produk) return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);

        if ($produk->Gambar && Storage::disk('public')->exists($produk->Gambar)) Storage::disk('public')->delete($produk->Gambar);

        $produk->delete();

        return response()->json(['success' => true, 'message' => 'Produk berhasil dihapus']);

    }

    // ─── PERMINTAAN AKTIF (PMT) ────────────────────────────────────────────────

    public function getPermintaanMasuk(Request $request)
    {
        $produsen   = $request->user();
        $permintaan = Permintaan::whereHas('produk', function ($q) use ($produsen) {
                            $q->where('Id_Produsen', $produsen->Id_Produsen);
                        })
                        // 🚀 PERBAIKAN: Tambahkan status Menunggu Validasi agar pesanan tampil di produsen
                       ->whereIn('Status', ['Pending', 'Menunggu', 'Diterima', 'Menunggu Validasi', 'MenungguVerifikasi', 'Dibayar', 'DiProses', 'pending', 'menunggu', 'diterima', 'menunggu validasi', 'menungguverifikasi', 'dibayar', 'diproses'])
                        ->with(['produk', 'mitra'])->get();

        $mapped = $permintaan->map(function ($item) {
            return [
                'id_permintaan'      => $item->Id_Permintaan,
                'nama_mitra'         => $item->mitra ? $item->mitra->Nama_Mitra : 'Mitra',
                'nama_produk'        => $item->produk ? $item->produk->Nama_Produk : '-',
                'jumlah_permintaan'  => $item->Jumlah_Diminta,
                'status'             => strtolower($item->Status),
                'tanggal_permintaan' => $item->Tanggal_Permintaan,
                'estimasi_total'     => $item->Jumlah_Diminta * ($item->produk ? $item->produk->Harga_Produksi : 0),
                'jenis_pesanan'      => 'permintaan'
            ];
        });
        return response()->json(['success' => true, 'data' => $mapped]);
    }

    public function terimaPermintaan(Request $request, $id)
    {
        $p = Permintaan::where('Id_Permintaan', $id)->first();
        if (!$p) return response()->json(['success' => false, 'message' => 'Tidak ditemukan'], 404);
        
        $p->Status = 'Diterima';
        $p->save();
        return response()->json(['success' => true, 'message' => 'Permintaan diterima, menunggu konfirmasi pembayaran']);
    }

    public function prosesPermintaan(Request $request, $id)
    {
        $p = Permintaan::where('Id_Permintaan', $id)->first();
        if (!$p) return response()->json(['success' => false, 'message' => 'Tidak ditemukan'], 404);

        $produk = Produk::where('Id_Produksi', $p->Id_Produksi)->first();
        if (!$produk) return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        if ($produk->Jumlah_Stok < $p->Jumlah_Diminta) {
            return response()->json(['success' => false, 'message' => "Stok tidak cukup. Tersisa: {$produk->Jumlah_Stok} kg"], 400);
        }

        $produk->Jumlah_Stok -= $p->Jumlah_Diminta;
        $produk->save();
        $p->Status = 'DiProses';
        $p->save();

        return response()->json(['success' => true, 'message' => 'Permintaan mulai diproses, stok berkurang']);
    }

    public function tolakPermintaan(Request $request, $id)
    {
        $p = Permintaan::where('Id_Permintaan', $id)->first();
        if (!$p) return response()->json(['success' => false, 'message' => 'Tidak ditemukan'], 404);
        $p->Status = 'Ditolak';
        $p->save();
        return response()->json(['success' => true]);
    }

    // ─── PEMBELIAN AKTIF (TRX) ─────────────────────────────────────────────────

    public function getRiwayatTransaksi(Request $request)
    {
        $produsen  = $request->user();
        $transaksi = Transaksi::whereHas('produk', function ($q) use ($produsen) {
                            $q->where('Id_Produsen', $produsen->Id_Produsen);
                        })
                        // 🚀 PERBAIKAN: Tambahkan status Menunggu Validasi di TRX juga
                        ->whereIn('Status', ['Pending', 'Menunggu', 'Menunggu Validasi', 'MenungguVerifikasi', 'Dibayar', 'DiProses', 'pending', 'menunggu', 'menunggu validasi', 'menungguverifikasi', 'dibayar', 'diproses'])
                        ->with(['produk', 'mitra'])->get();

        $mapped = $transaksi->map(function ($item) {
            return [
                'id_permintaan'      => $item->Id_Transaksi,
                'nama_mitra'         => $item->mitra ? $item->mitra->Nama_Mitra : 'Mitra',
                'nama_produk'        => $item->produk ? $item->produk->Nama_Produk : '-',
                'jumlah_permintaan'  => $item->Jumlah,
                'status'             => strtolower($item->Status),
                'tanggal_permintaan' => $item->Tanggal_Transaksi,
                'estimasi_total'     => $item->Total_Harga,
                'jenis_pesanan'      => 'pembelian'
            ];
        });
        return response()->json(['success' => true, 'data' => $mapped]);
    }

    public function prosesTransaksi(Request $request, $id)
    {
        $t = Transaksi::where('Id_Transaksi', $id)->first();
        if (!$t) return response()->json(['success' => false], 404);

        $produk = Produk::where('Id_Produksi', $t->Id_Produksi)->first();
        if (!$produk) return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        if ($produk->Jumlah_Stok < $t->Jumlah) {
            return response()->json(['success' => false, 'message' => "Stok tidak cukup. Tersisa: {$produk->Jumlah_Stok} kg"], 400);
        }

        $produk->Jumlah_Stok -= $t->Jumlah;
        $produk->save();
        $t->Status = 'DiProses';
        $t->save();

        return response()->json(['success' => true, 'message' => 'Pesanan diproses, stok diperbarui']);
    }

    public function tolakTransaksi(Request $request, $id)
    {
        $t = Transaksi::where('Id_Transaksi', $id)->first();
        if (!$t) return response()->json(['success' => false], 404);
        $t->Status = 'Ditolak';
        $t->save();
        return response()->json(['success' => true]);
    }

    public function getRiwayatTotalSelesai(Request $request) { /* kode riwayat gabungan kamu... */ }
}