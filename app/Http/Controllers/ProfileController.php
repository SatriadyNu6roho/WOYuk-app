<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserProfile;
use App\UserSetting;
use App\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilkan Halaman Profil Sendiri (Mendukung Filter Opsi 1H, 7H, 30H via AJAX / Normal)
     */
    public function show(Request $request)
    {
        $user = Auth::user()->load(['profile', 'setting', 'streak']);
        $userTimezone = $user->setting->timezone ?? 'Asia/Jakarta';

        // 1. Total Aktivitas Selesai per Kategori (Sinkron 100% dengan Arsip)
        $stats = [
            'running' => Activity::where('user_id', $user->id)->where('status', 'completed')->where('sport_type', 'running')->count(),
            'walking' => Activity::where('user_id', $user->id)->where('status', 'completed')->where('sport_type', 'walking')->count(),
            'cycling' => Activity::where('user_id', $user->id)->where('status', 'completed')->where('sport_type', 'cycling')->count(),
            'total_distance' => round(Activity::where('user_id', $user->id)->where('status', 'completed')->sum('distance_meters') / 1000, 2),
        ];

        // 2. Filter Rentang Waktu Grafik (1d = 1 Hari/24 Jam, 7d = 7 Hari, 30d = 30 Hari)
        $period = $request->get('period', '7d');

        $chartLabels = [];
        $runningData = [];
        $walkingData = [];
        $cyclingData = [];

        if ($period === '1d') {
            // Rentang 1 Hari: Interval per 3 jam dalam 24 jam terakhir
            for ($i = 21; $i >= 0; $i -= 3) {
                $startTime = Carbon::now($userTimezone)->subHours($i + 3);
                $endTime = Carbon::now($userTimezone)->subHours($i);
                
                $chartLabels[] = $endTime->format('H:i');

                $runningData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'running')
                    ->whereBetween('start_time', [$startTime, $endTime])
                    ->sum('distance_meters') / 1000, 2);

                $walkingData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'walking')
                    ->whereBetween('start_time', [$startTime, $endTime])
                    ->sum('distance_meters') / 1000, 2);

                $cyclingData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'cycling')
                    ->whereBetween('start_time', [$startTime, $endTime])
                    ->sum('distance_meters') / 1000, 2);
            }
        } elseif ($period === '30d') {
            // Rentang 30 Hari Terakhir
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now($userTimezone)->subDays($i)->toDateString();
                $chartLabels[] = Carbon::parse($date)->translatedFormat('d M');

                $runningData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'running')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $walkingData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'walking')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $cyclingData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'cycling')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);
            }
        } else {
            // Default 7 Hari Terakhir
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now($userTimezone)->subDays($i)->toDateString();
                $chartLabels[] = Carbon::parse($date)->translatedFormat('d M');

                $runningData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'running')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $walkingData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'walking')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $cyclingData[] = round(Activity::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'cycling')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);
            }
        }

        // Response khusus permintaan AJAX saat tombol 1H / 7H / 30H diklik
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'chartLabels' => $chartLabels,
                'runningData' => $runningData,
                'walkingData' => $walkingData,
                'cyclingData' => $cyclingData,
            ]);
        }

        return view('profile.show', compact('user', 'stats', 'chartLabels', 'runningData', 'walkingData', 'cyclingData', 'period'));
    }

    /**
     * Tampilkan Form Pengaturan Profil & Sistem
     */
    public function editSettings()
    {
        $user = Auth::user();

        $profile = UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->email]
        );

        $setting = UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['timezone' => 'Asia/Jakarta']
        );

        return view('profile.edit', compact('user', 'profile', 'setting'));
    }

    /**
     * Simpan / Update Data Profil, Username, Bio & Avatar
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $profile = UserProfile::firstOrCreate(['user_id' => $user->id]);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:50|unique:user_profiles,username,' . $profile->id,
            'bio' => 'nullable|string|max:255',
            'weight_kg' => 'nullable|numeric|min:20|max:300',
            'height_cm' => 'nullable|numeric|min:50|max:250',
            'gender' => 'nullable|in:male,female',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'bio' => $request->bio,
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
            'gender' => $request->gender,
        ];

        if ($request->hasFile('avatar')) {
            if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($data);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update Pengaturan Timezone
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string|max:50',
        ]);

        $user = Auth::user();
        
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['timezone' => $request->timezone]
        );

        return redirect()->back()->with('success', 'Pengaturan zona waktu berhasil diperbarui!');
    }
}