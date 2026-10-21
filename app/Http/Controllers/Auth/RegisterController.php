<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\UserProfile;
use App\UserSetting;
use App\Streak;
use App\ActivityTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // 1. Validasi Input Ketat
        $request->validate([
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar di WOYuk!.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // 2. DB Transaction untuk Menjamin Seluruh Data Pengguna Baru Dibuat Bersamaan
        DB::beginTransaction();
        try {
            // Buat Akun User Utama (Default role selalu 'user')
            $user = User::create([
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => 'user', // Keamanan: Pendaftaran publik SELALU ber-role 'user'
            ]);

            // Buat username unik dari email
            $baseUsername = explode('@', $user->email)[0];
            $cleanBase = Str::slug($baseUsername);
            $username = ($cleanBase ?: 'user') . '_' . rand(100, 999);

            // Buat User Profile Default (SUDAH MEMASUKKAN USERNAME)
            UserProfile::create([
                'user_id' => $user->id,
                'name' => ucfirst($baseUsername),
                'username' => $username,
                'weight_kg' => 60.00,
            ]);

            // Buat User Setting Default
            UserSetting::create([
                'user_id' => $user->id,
                'theme' => 'dark',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]);

            // Buat Record Streak Awal
            Streak::create([
                'user_id' => $user->id,
                'current_streak' => 0,
                'longest_streak' => 0,
            ]);

            // Buat Target Harian Default
            ActivityTarget::create([
                'user_id' => $user->id,
                'daily_distance_km' => 5.00,
                'daily_calories' => 300,
                'is_active' => true,
            ]);

            DB::commit();

            // Auto Login setelah registrasi
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Selamat datang di WOYuk! Akunmu berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mendaftar: ' . $e->getMessage()]);
        }
    }
}