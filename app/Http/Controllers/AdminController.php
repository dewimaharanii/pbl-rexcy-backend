<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Produsen;
use App\Models\MitraHilir;
use App\Models\Produk;
use App\Models\Permintaan;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\PencairanDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Tambahan untuk deteksi error database

class AdminController extends Controller
{
    // ── AUTH ──────────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate(['Username' => 'required', 'Kata_Sandi' => 'required']);
        $admin = Admin::where('Username', $request->Username)->first();

        if (!$admin || !Hash::check($request->Kata_Sandi, $admin->Kata_Sandi)) {
            return response()->json(['success' => false, 'message' => 'Username atau password salah'], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;
        return response()->json(['success' => true, 'message' => 'Login berhasil', 'token' => $token, 'user' => $admin]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // ── DASHBOARD STATS ───────────────────────────────────────

    public function dashboard()
    {
        $totalTransaksi = Transaksi::count() + Permintaan::count();

        $transaksiMenunggu = Transaksi::whereIn('Status', ['Pending', 'Menunggu'])->count()
                           + Permintaan::whereIn('Status', ['Pending', 'Menunggu'])->count();

        $pendapatanTrx = Transaksi::where('Status', 'Selesai')->sum('Total_Harga');

        $pendapatanPmt = Permintaan::where('Status', 'Selesai')
                            ->with('produk')
                            ->get()
                            ->sum(fn($item) => $item->Jumlah_Diminta * ($item->produk->Harga_Produksi ?? 0));

        $trxTerbaru = Transaksi::with(['produk.produsen', 'mitra'])
                        ->orderBy('Tanggal_Transaksi', 'desc')
                        ->limit(5)->get()
                        ->map(fn($item) => [
                            'id'            => $item->Id_Transaksi,
                            'nama_produsen' => $item->produk->produsen->Nama_Produsen ?? '-',
                            'nama_mitra'    => $item->mitra->Nama_Mitra ?? '-',
                            'nama_produk'   => $item->produk->Nama_Produk ?? '-',
                            'jumlah'        => $item->Jumlah,
                            'total_harga'   => (float) $item->Total_Harga,
                            'status'        => $item->Status,
                            'tanggal'       => $item->Tanggal_Transaksi,
                            'jenis'         => 'pembelian',
                        ]);

        $pmtTerbaru = Permintaan::with(['produk.produsen', 'mitra'])
                        ->orderBy('Tanggal_Permintaan', 'desc')
                        ->limit(5)->get()
                        ->map(fn($item) => [
                            'id'            => $item->Id_Permintaan,
                            'nama_produsen' => $item->produk->produsen->Nama_Produsen ?? '-',
                            'nama_mitra'    => $item->mitra->Nama_Mitra ?? '-',
                            'nama_produk'   => $item->produk->Nama_Produk ?? '-',
                            'jumlah'        => $item->Jumlah_Diminta,
                            'total_harga'   => (float) ($item->Jumlah_Diminta * ($item->produk->Harga_Produksi ?? 0)),
                            'status'        => $item->Status,
                            'tanggal'       => $item->Tanggal_Permintaan,
                            'jenis'         => 'permintaan',
                        ]);

        $transaksiTerbaru = $trxTerbaru->concat($pmtTerbaru)
                            ->sortByDesc('tanggal')
                            ->take(5)
                            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_produsen'     => Produsen::count(),
                'total_mitra'        => MitraHilir::count(),
                'total_produksi'     => Produk::count(),
                'total_transaksi'    => $totalTransaksi,
                'transaksi_menunggu' => $transaksiMenunggu,
                'total_pendapatan'   => $pendapatanTrx + $pendapatanPmt,
                'transaksi_terbaru'  => $transaksiTerbaru,
            ]
        ]);
    }

    // ── PRODUSEN ──────────────────────────────────────────────

    public function getProdusen()
    {
        return response()->json(['success' => true, 'data' => Produsen::all()]);
    }

    public function tambahProdusen(Request $request)
    {
        $request->validate([
            'Nama_Produsen' => 'required',
            'Username'      => 'required|unique:produsen,Username',
            'Kata_Sandi'    => 'required|min:6',
            'No_HP'         => 'required',
            'Alamat'        => 'required',
            'Jenis_Usaha'   => 'required|in:Nelayan,KUB,Lainnya',
        ]);

        $last  = Produsen::orderBy('Id_Produsen', 'desc')->first();
        $newId = $last ? 'PRN' . str_pad((int)substr($last->Id_Produsen, 3) + 1, 3, '0', STR_PAD_LEFT) : 'PRN001';

        $produsen = Produsen::create([
            'Id_Produsen'   => $newId,
            'Nama_Produsen' => $request->Nama_Produsen,
            'Username'      => $request->Username,
            'Kata_Sandi'    => Hash::make($request->Kata_Sandi),
            'No_HP'         => $request->No_HP,
            'Alamat'        => $request->Alamat,
            'Jenis_Usaha'   => $request->Jenis_Usaha,
        ]);

        return response()->json(['success' => true, 'message' => 'Produsen berhasil ditambahkan', 'data' => $produsen], 201);
    }

    public function updateProdusen(Request $request, $id)
    {
        $produsen = Produsen::find($id);
        if (!$produsen) return response()->json(['success' => false, 'message' => 'Produsen tidak ditemukan'], 404);

        $produsen->update($request->only(['Nama_Produsen', 'No_HP', 'Alamat', 'Jenis_Usaha']));
        if ($request->filled('Kata_Sandi')) $produsen->Kata_Sandi = Hash::make($request->Kata_Sandi);
        $produsen->save();

        return response()->json(['success' => true, 'message' => 'Produsen berhasil diperbarui', 'data' => $produsen]);
    }

    public function hapusProdusen($id)
    {
        $produsen = Produsen::find($id);
        if (!$produsen) return response()->json(['success' => false, 'message' => 'Produsen tidak ditemukan'], 404);
        $produsen->delete();
        return response()->json(['success' => true, 'message' => 'Produsen berhasil dihapus']);
    }

    // ── MITRA ─────────────────────────────────────────────────

    public function getMitra()
    {
        return response()->json(['success' => true, 'data' => MitraHilir::all()]);
    }

    public function hapusMitra($id)
    {
        $mitra = MitraHilir::find($id);
        if (!$mitra) return response()->json(['success' => false, 'message' => 'Mitra tidak ditemukan'], 404);
        $mitra->delete();
        return response()->json(['success' => true, 'message' => 'Mitra berhasil dihapus']);
    }

    // ── PRODUKSI ──────────────────────────────────────────────

    public function getProduksi()
    {
        $produksi = Produk::with('produsen')->orderBy('Dibuat_Pada', 'desc')->get();
        return response()->json(['success' => true, 'data' => $produksi]);
    }

    // ── TRANSAKSI ─────────────────────────────────────────────

    public function getTransaksi()
    {
        // ✅ FUNGSI INI SUDAH KEMBALI SEPERTI SEMULA
        $trx = Transaksi::with(['mitra', 'produk.produsen'])
                ->orderBy('Tanggal_Transaksi', 'desc')
                ->get()
                ->map(fn($item) => [
                    'id'                 => $item->Id_Transaksi,
                    'nama_mitra'         => $item->mitra->Nama_Mitra ?? '-',
                    'nama_produsen'      => $item->produk->produsen->Nama_Produsen ?? '-',
                    'nama_produk'        => $item->produk->Nama_Produk ?? '-',
                    'jumlah'             => $item->Jumlah,
                    'total_harga'        => (float) $item->Total_Harga,
                    'status'             => $item->Status,
                    'tanggal'            => $item->Tanggal_Transaksi,
                    'jenis'              => 'pembelian',
                    'konfirmasi_mitra'   => $item->Status === 'Selesai' ? 'sudah_sampai' : null,
                    'konfirmasi_admin'   => null,
                    'nama_pemesan'       => $item->nama_pemesan ?? '',
                    'no_telp'            => $item->no_telp ?? '',
                    'alamat_pemesan'     => $item->alamat_pemesan ?? '',
                ]);

        $pmt = Permintaan::with(['mitra', 'produk.produsen'])
                ->orderBy('Tanggal_Permintaan', 'desc')
                ->get()
                ->map(fn($item) => [
                    'id'                 => $item->Id_Permintaan,
                    'nama_mitra'         => $item->mitra->Nama_Mitra ?? '-',
                    'nama_produsen'      => $item->produk->produsen->Nama_Produsen ?? '-',
                    'nama_produk'        => $item->produk->Nama_Produk ?? '-',
                    'jumlah'             => $item->Jumlah_Diminta,
                    'total_harga'        => (float) ($item->Jumlah_Diminta * ($item->produk->Harga_Produksi ?? 0)),
                    'status'             => $item->Status,
                    'tanggal'            => $item->Tanggal_Permintaan,
                    'jenis'              => 'permintaan',
                    'konfirmasi_mitra'   => $item->Status === 'Selesai' ? 'sudah_sampai' : null,
                    'konfirmasi_admin'   => null,
                    'nama_pemesan'       => $item->nama_pemesan ?? '',
                    'no_telp'            => $item->no_telp ?? '',
                    'alamat_pemesan'     => $item->alamat_pemesan ?? '',
                ]);

        $gabungan = $trx->concat($pmt)->sortByDesc('tanggal')->values();

        return response()->json(['success' => true, 'data' => $gabungan]);
    }

    // ── PEMBAYARAN (ADMIN) ────────────────────────────────────

    public function getPembayaran()
    {
        try {
            $data = DB::table('pembayaran')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function($item) {
                    // Cari tanggal pembuatan dari tabel asal (transaksi atau permintaan)
                    $tanggalPembuatan = null;
                    if (str_starts_with($item->id_transaksi ?? '', 'PMT')) {
                        $pmt = DB::table('permintaan')
                            ->where('Id_Permintaan', $item->id_transaksi)
                            ->select('Tanggal_Permintaan')
                            ->first();
                        $tanggalPembuatan = $pmt ? $pmt->Tanggal_Permintaan : null;
                    } else {
                        $trx = DB::table('transaksi')
                            ->where('Id_Transaksi', $item->id_transaksi)
                            ->select('Tanggal_Transaksi')
                            ->first();
                        $tanggalPembuatan = $trx ? $trx->Tanggal_Transaksi : null;
                    }

                    return [
                        'id_pesanan'        => $item->id_transaksi,
                        'jenis'             => $item->jenis,
                        'mitra'             => $item->nama_mitra ?? '-',
                        'produk'            => $item->nama_produk ?? '-',
                        'total'             => $item->jumlah_bayar,
                        'tanggal'           => $item->tanggal_bayar,
                        'tanggal_pembuatan' => $tanggalPembuatan,
                        'status'            => $item->status_pembayaran ?? 'Menunggu Konfirmasi',
                        'bukti'             => $item->bukti_transfer,
                        'bukti_url'         => $item->bukti_transfer
                            ? url('api/file/bukti/' . basename($item->bukti_transfer))
                            : null,
                    ];
                });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error Backend: ' . $e->getMessage()]);
        }
    }

    public function konfirmasiPembayaran(Request $request, $jenis, $id)
{
    try {
        $aksi = $request->input('action', 'terima'); 
        
        // 1. Update status di tabel pembayaran
        DB::table('pembayaran')
            ->where('id_transaksi', $id)
            ->update(['status_pembayaran' => $aksi === 'terima' ? 'Dikonfirmasi' : 'Ditolak']);

        // 2. Update status di tabel Induk (PMT / TRX)
        if (str_starts_with($id, 'PMT')) {
            $pesanan = \App\Models\Permintaan::where('Id_Permintaan', $id)->first();
            if ($pesanan) {
                $pesanan->Status = $aksi === 'terima' ? 'Dibayar' : 'Ditolak'; 
                $pesanan->save();
            }
        } else {
            $pesanan = \App\Models\Transaksi::where('Id_Transaksi', $id)->first();
            if ($pesanan) {
                $pesanan->Status = $aksi === 'terima' ? 'Dibayar' : 'Ditolak';
                $pesanan->save();
            }
        }
     
        return response()->json([
            'success' => true,
            'message' => $aksi === 'terima' ? 'Pembayaran dikonfirmasi' : 'Pembayaran ditolak',
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error Validasi: ' . $e->getMessage()]);
    }
}

// ── PENCAIRAN DANA (ADMIN) ─────────────────────────────────

public function getPencairan()
{
    $data = PencairanDana::with('produsen')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get()
            ->map(fn($item) => [
                'id_pencairan'          => $item->id_pencairan,
                'nama_produsen'         => $item->produsen->Nama_Produsen ?? '-',
                'jumlah_dana'           => (int) $item->jumlah_dana,
                'nama_bank'             => $item->nama_bank,
                'no_rekening'           => $item->no_rekening,
                'nama_pemilik_rekening' => $item->nama_pemilik_rekening,
                'status'                => $item->status,
                'keterangan_admin'      => $item->keterangan_admin,
                'tanggal_pengajuan'     => $item->tanggal_pengajuan,
                'tanggal_diproses'      => $item->tanggal_diproses,
            ]);

    return response()->json(['success' => true, 'data' => $data]);
}

public function prosesPencairan(Request $request, $id)
{
    try {
        $aksi = $request->input('action', 'terima'); // 'terima' atau 'tolak'

        $pencairan = PencairanDana::where('id_pencairan', $id)->first();
        if (!$pencairan) {
            return response()->json(['success' => false, 'message' => 'Pengajuan tidak ditemukan'], 404);
        }

        if ($pencairan->status !== 'Menunggu') {
            return response()->json(['success' => false, 'message' => 'Pengajuan ini sudah diproses sebelumnya'], 400);
        }

        $pencairan->status = $aksi === 'terima' ? 'Disetujui' : 'Ditolak';
        $pencairan->keterangan_admin = $request->input('keterangan', null);
        $pencairan->tanggal_diproses = now();
        $pencairan->save();

        return response()->json([
            'success' => true,
            'message' => $aksi === 'terima' ? 'Pencairan dana disetujui' : 'Pencairan dana ditolak',
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

public function selesaikanPencairan($id)
{
    try {
        $pencairan = PencairanDana::where('id_pencairan', $id)->first();
        if (!$pencairan) {
            return response()->json(['success' => false, 'message' => 'Pengajuan tidak ditemukan'], 404);
        }

        if ($pencairan->status !== 'Disetujui') {
            return response()->json(['success' => false, 'message' => 'Pengajuan harus berstatus Disetujui dulu'], 400);
        }

        $pencairan->status = 'Selesai';
        $pencairan->tanggal_diproses = now();
        $pencairan->save();

        return response()->json(['success' => true, 'message' => 'Pencairan dana ditandai selesai']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
   
}