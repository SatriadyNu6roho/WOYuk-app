<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SportSelectorService;
use App\Activity;
use App\ActivityTarget;
use App\Services\ActivityValidationService;
use App\Services\StreakService;
use Carbon\Carbon;
use Exception;

class ActivitySessionController extends Controller
{
    /**
     * Inisialisasi Sesi Tracking Baru & Tampilkan Screen Tracking
     */
    public function start(Request $request)
    {
        $sportType = $request->input('sport_type', 'running');

        if (!SportSelectorService::isValid($sportType)) {
            return redirect()->route('dashboard')->with('warning', 'Jenis olahraga tidak valid.');
        }

        $sportConfig = SportSelectorService::getConfig($sportType);
        $user = Auth::user();

        try {
            // BATALKAN SELURUH AKTIVITAS GANTUNG/LAMA YANG MASIH STATUS 'ACTIVE'
            Activity::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            // BUAT RECORD AKTIVITAS BARU DENGAN START_TIME SEGAR (WAKTU SEKARANG)
            $activeActivity = Activity::create([
                'user_id' => $user->id,
                'sport_type' => $sportType,
                'status' => 'active',
                'start_time' => Carbon::now(),
                'distance_meters' => 0.00,
                'duration_seconds' => 0,
                'avg_speed_kmh' => 0.00,
                'elevation_gain_m' => 0.00,
                'calories_burned' => 0,
            ]);

            return view('activity.tracking', compact('activeActivity', 'sportConfig'));

        } catch (Exception $e) {
            Log::error('Error saat memulai aktivitas: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('warning', 'Gagal memulai sesi aktivitas baru. Silakan coba lagi.');
        }
    }

    /**
     * Hentikan & Validasi Aktivitas
     */
    public function stop(Request $request, $id)
    {
        try {
            $activity = Activity::where('id', $id)
                ->where('user_id', auth()->id())
                ->where('status', 'active')
                ->first();

            if (!$activity) {
                return redirect()->route('dashboard')->with('warning', 'Aktivitas tidak ditemukan atau sudah dihentikan.');
            }

            // Ambil data paling mutakhir hasil sync API terakhir
            $activity->refresh();

            // Validasi dan Selesaikan Sesi Aktivitas via Validation Service
            $result = ActivityValidationService::validateAndComplete($activity);

            if (isset($result['isValid']) && $result['isValid']) {
                // Pembaruan streak otomatis saat aktivitas valid
                StreakService::updateStreak($activity->user_id);

                // Cek ketercapaian target harian setelah sesi ini rampung
                $user = auth()->user();
                $userTimezone = $user->setting->timezone ?? 'Asia/Jakarta';
                $todayDate = Carbon::now($userTimezone)->toDateString();

                $target = ActivityTarget::where('user_id', $user->id)->where('is_active', true)->first();

                if ($target) {
                    $todayActivities = Activity::where('user_id', $user->id)
                        ->where('status', 'completed')
                        ->whereDate('start_time', $todayDate)
                        ->get();

                    $todayDistKm = round($todayActivities->sum('distance_meters') / 1000, 2);
                    $todayCal = $todayActivities->sum('calories_burned');

                    $distAchieved = $target->daily_distance_km > 0 && $todayDistKm >= $target->daily_distance_km;
                    $calAchieved = $target->daily_calories > 0 && $todayCal >= $target->daily_calories;

                    if ($distAchieved || $calAchieved) {
                        $achievedType = ($distAchieved && $calAchieved) ? 'both' : ($distAchieved ? 'distance' : 'calories');
                        session()->flash('target_just_achieved', $achievedType);
                    }
                }

                return redirect()->route('activity.result', $id)->with('success', $result['message']);
            }

            return redirect()->route('dashboard')->with('warning', $result['message'] ?? 'Aktivitas tidak memenuhi syarat kelayakan.');

        } catch (Exception $e) {
            Log::error('Error saat menghentikan aktivitas ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('dashboard')->with('warning', 'Terjadi kesalahan sistem saat menyimpan aktivitas.');
        }
    }

    /**
     * Tampilkan Halaman Kartu Hasil Aktivitas
     */
    public function result($id)
    {
        try {
            $activity = Activity::where('id', $id)
                ->where('user_id', auth()->id())
                ->where('status', 'completed')
                ->firstOrFail();

            $sportConfig = SportSelectorService::getConfig($activity->sport_type);
            $user = auth()->user();

            // Ambil titik koordinat GPS untuk dirender di peta hasil
            $points = $activity->points()
                ->orderBy('recorded_at', 'asc')
                ->get(['latitude', 'longitude']);

            $targetAchievedType = session('target_just_achieved');

            return view('activity.result', compact('activity', 'sportConfig', 'user', 'points', 'targetAchievedType'));
        } catch (Exception $e) {
            Log::error('Error menampilkan hasil aktivitas ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('dashboard')->with('warning', 'Data hasil aktivitas tidak ditemukan.');
        }
    }
}