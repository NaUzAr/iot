<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // [TAMBAHAN PENTING] Untuk hashing password
use App\Models\User;                 // [TAMBAHAN PENTING] Untuk memanggil model User

class AuthController extends Controller
{
    // Menampilkan Halaman Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Memproses Login
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Cek ke Database otomatis
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Mencegah Session Fixation attack

            // Detect PWA mode and store in session
            if ($request->has('pwa') || $request->session()->get('is_pwa')) {
                $request->session()->put('is_pwa', true);
                return redirect()->route('monitoring.index'); // PWA → langsung ke monitoring
            }

            return redirect()->intended('/'); // Web biasa → halaman yang dituju
        }

        // 3. Jika gagal, kembalikan error
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    // Memproses Registrasi
    public function register(Request $request)
    {
        // A. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            // 'confirmed' mewajibkan adanya field 'password_confirmation' di form HTML
        ]);

        // B. Buat User Baru di Database
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password
        ]);

        // C. Langsung Login otomatis setelah daftar
        Auth::login($user);

        // D. Lempar ke Dashboard
        return redirect()->route('home'); // Pastikan route dengan nama 'home' ada di web.php
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}