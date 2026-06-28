<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Produsen;
use App\Models\MitraHilir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Login Admin
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $admin
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // List semua produsen
    public function getProdusen()
    {
        $produsen = Produsen::all();
        return response()->json(['success' => true, 'data' => $produsen]);
    }

    // Verifikasi produsen
    public function verifikasiProdusen($id)
    {
        $produsen = Produsen::find($id);
        if (!$produsen) {
            return response()->json(['success' => false, 'message' => 'Produsen tidak ditemukan'], 404);
        }
        $produsen->status_verifikasi = 'terverifikasi';
        $produsen->save();
        return response()->json(['success' => true, 'message' => 'Produsen berhasil diverifikasi']);
    }

    // List semua mitra hilir
    public function getMitra()
    {
        $mitra = MitraHilir::all();
        return response()->json(['success' => true, 'data' => $mitra]);
    }

    // Verifikasi mitra hilir
    public function verifikasiMitra($id)
    {
        $mitra = MitraHilir::find($id);
        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Mitra tidak ditemukan'], 404);
        }
        $mitra->status_verifikasi = 'terverifikasi';
        $mitra->save();
        return response()->json(['success' => true, 'message' => 'Mitra berhasil diverifikasi']);
    }
}