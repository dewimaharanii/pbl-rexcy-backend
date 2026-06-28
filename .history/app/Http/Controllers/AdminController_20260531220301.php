public function login(Request $request)
{
    $request->validate([
        'Username' => 'required',
        'Kata_Sandi' => 'required'
    ]);

    $admin = Admin::where('Username', $request->Username)->first();

    if (!$admin || !Hash::check($request->Kata_Sandi, $admin->Kata_Sandi)) {
        return response()->json([
            'success' => false,
            'message' => 'Username atau password salah'
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