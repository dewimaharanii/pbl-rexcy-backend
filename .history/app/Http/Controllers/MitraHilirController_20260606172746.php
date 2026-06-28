// Update Profile Mitra
public function updateProfile(Request $request)
{
    $mitra = $request->user();

    $request->validate([
        'Nama_Mitra' => 'required',
        'No_HP'      => 'nullable',
    ]);

    $mitra->Nama_Mitra = $request->Nama_Mitra;

    if ($request->filled('No_HP')) {
        $mitra->No_HP = $request->No_HP;
    }

    // Ganti password hanya kalau dikirim
    if ($request->filled('Kata_Sandi_Baru')) {
        $mitra->Kata_Sandi = Hash::make($request->Kata_Sandi_Baru);
    }

    $mitra->save();

    return response()->json([
        'success' => true,
        'message' => 'Profil berhasil diperbarui',
        'user'    => $mitra
    ]);
}