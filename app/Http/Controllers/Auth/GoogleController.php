<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\UserProfile;
use App\UserSetting;
use App\Streak;
use App\ActivityTarget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman autentikasi Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback data yang dikirim balik oleh Google
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            DB::beginTransaction();

            // 1. Cek apakah user dengan google_id atau email tersebut sudah ada
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', strtolower(trim($googleUser->getEmail())))
                        ->first();

            if (!$user) {
                // 2. Jika belum ada, buat user baru
                $user = User::create([
                    'email' => strtolower(trim($googleUser->getEmail())),
                    'google_id' => $googleUser->getId(),
                    'password' => null,
                    'role' => 'user', // Bebas password karena menggunakan OAuth
                ]);

                $baseUsername = explode('@', $user->email)[0];
                $username = Str::slug($baseUsername) . '_' . rand(100, 999);

                // Profil Default
                UserProfile::create([
                    'user_id' => $user->id,
                    'name' => $googleUser->getName() ?? ucfirst($baseUsername),
                    'username' => $username,
                    'avatar' => $googleUser->getAvatar(),
                    'weight_kg' => 60.00,
                ]);

                // Setting Default
                UserSetting::create([
                    'user_id' => $user->id,
                    'theme' => 'dark',
                    'language' => 'id',
                    'timezone' => 'Asia/Jakarta',
                ]);

                // Streak Awal
                Streak::create([
                    'user_id' => $user->id,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                ]);

                // Target Harian Awal
                ActivityTarget::create([
                    'user_id' => $user->id,
                    'daily_distance_km' => 5.00,
                    'daily_calories' => 300,
                    'is_active' => true,
                ]);
            } else {
                // Update google_id jika user lama login via Google
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            DB::commit();

            // Auto Login
            Auth::login($user, true);

            return redirect()->intended('/dashboard')->with('success', 'Berhasil login nganggo Akun Google!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('login')->withErrors(['error' => 'Gagal autentikasi Google: ' . $e->getMessage()]);
        }
    }
}