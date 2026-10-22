<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Activity;
use App\Streak;
use App\ActivityTarget;
use App\Services\SportSelectorService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user()->load(['profile', 'setting', 'streak', 'activeTarget']);

        $userTimezone = $user->setting->timezone ?? 'Asia/Jakarta';
        $todayDate = Carbon::now($userTimezone)->toDateString();

        $streak = $user->streak ?? Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        // ==========================================
        // LOGIKA BARU: STATUS STREAK (AKTIF / TIDUR / HANGUS)
        // ==========================================
        $lastDate = $streak->last_qualifying_date 
            ? Carbon::parse($streak->last_qualifying_date, $userTimezone)->toDateString() 
            : null;
            
        $today = Carbon::now($userTimezone)->toDateString();
        $yesterday = Carbon::yesterday($userTimezone)->toDateString();
        $twoDaysAgo = Carbon::now($userTimezone)->subDays(2)->toDateString();

        $streakStatus = 'active'; // Default
        $displayStreak = $streak->current_streak;

        if ($lastDate === $twoDaysAgo) {
            // Terakhir olahraga kemarin lusa = Sedang Tidur (Recovery)
            $streakStatus = 'sleeping';
        } elseif ($lastDate !== null && $lastDate < $twoDaysAgo) {
            // Bolos lebih dari 1 hari = Hangus
            $streakStatus = 'broken';
            $displayStreak = 0; // Di UI tampilkan 0
        }

        $target = $user->activeTarget ?? ActivityTarget::firstOrCreate(
            ['user_id' => $user->id],
            ['daily_distance_km' => 5.00, 'daily_calories' => 300, 'is_active' => true]
        );

        $todayActivities = Activity::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('start_time', $todayDate)
            ->get();

        $todayDistanceMeters = $todayActivities->sum('distance_meters');
        $todayDistanceKm = round($todayDistanceMeters / 1000, 2);
        $todayCalories = $todayActivities->sum('calories_burned');

        $targetDistanceKm = (float) ($target->daily_distance_km ?? 5.00);
        $targetCalories = (int) ($target->daily_calories ?? 300);

        // Kalkulasi Persentase Progress
        $distanceProgressPercent = $targetDistanceKm > 0 
            ? min(100, round(($todayDistanceKm / $targetDistanceKm) * 100)) 
            : 0;
            
        $caloriesProgressPercent = $targetCalories > 0 
            ? min(100, round(($todayCalories / $targetCalories) * 100)) 
            : 0;

        // Deteksi Pencapaian Target Harian (Jarak / Kalori / Keduanya)
        $isDistanceAchieved = $targetDistanceKm > 0 && $todayDistanceKm >= $targetDistanceKm;
        $isCaloriesAchieved = $targetCalories > 0 && $todayCalories >= $targetCalories;
        $isBothAchieved = $isDistanceAchieved && $isCaloriesAchieved;

        $targetAchievedType = null;
        if ($isBothAchieved) {
            $targetAchievedType = 'both';
        } elseif ($isDistanceAchieved) {
            $targetAchievedType = 'distance';
        } elseif ($isCaloriesAchieved) {
            $targetAchievedType = 'calories';
        }

        $sports = SportSelectorService::getAllConfigs();

        // TANGKAP FLASH SESSION MILESTONE STREAK (10, 50, 100)
        $streakMilestone = session('streak_milestone_achieved');

        // Agregasi Data Histori Permingguan (4 Minggu Terakhir)
        $weeklyHistory = [];
        for ($i = 0; $i < 4; $i++) {
            $startOfWeek = Carbon::now($userTimezone)->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = Carbon::now($userTimezone)->subWeeks($i)->endOfWeek(Carbon::SUNDAY);

            $weekActivities = Activity::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereBetween('start_time', [$startOfWeek->toDateTimeString(), $endOfWeek->toDateTimeString()])
                ->get();

            $totalDistM = $weekActivities->sum('distance_meters');
            $totalCal = $weekActivities->sum('calories_burned');
            $totalDur = $weekActivities->sum('duration_seconds');
            $workoutCount = $weekActivities->count();

            $dayCounts = [
                'Sen' => 0, 'Sel' => 0, 'Rab' => 0,
                'Kam' => 0, 'Jum' => 0, 'Sab' => 0, 'Min' => 0
            ];
            $dayMap = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 0 => 'Min'];

            $timeSlots = [
                'Pagi' => 0,
                'Siang' => 0,
                'Sore' => 0,
                'Malam' => 0,
            ];

            foreach ($weekActivities as $act) {
                $actTime = Carbon::parse($act->start_time)->timezone($userTimezone);
                
                $dayName = $dayMap[$actTime->dayOfWeek] ?? 'Sen';
                $dayCounts[$dayName]++;

                $hour = $actTime->hour;
                $minute = $actTime->minute;
                $timeVal = $hour + ($minute / 60);

                if ($timeVal >= 5 && $timeVal < 10) {
                    $timeSlots['Pagi']++;
                } elseif ($timeVal >= 10 && $timeVal < 15) {
                    $timeSlots['Siang']++;
                } elseif ($timeVal >= 15 && $timeVal < 18.5) {
                    $timeSlots['Sore']++;
                } else {
                    $timeSlots['Malam']++;
                }
            }

            $weekLabel = $i === 0 ? 'Minggu Ini' : ($i === 1 ? 'Minggu Lalu' : "{$i} Minggu Lalu");

            $weeklyHistory[] = [
                'week_number' => $i,
                'label' => $weekLabel,
                'date_range' => $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y'),
                'total_distance_km' => round($totalDistM / 1000, 2),
                'total_calories' => $totalCal,
                'total_duration_sec' => $totalDur,
                'workout_count' => $workoutCount,
                'days_breakdown' => $dayCounts,
                'times_breakdown' => $timeSlots,
            ];
        }

        return view('dashboard', compact(
            'user',
            'streak',
            'streakStatus',    // <-- DILEMPAR KE VIEW
            'displayStreak',   // <-- DILEMPAR KE VIEW
            'target',
            'todayDistanceKm',
            'todayCalories',
            'targetDistanceKm',
            'targetCalories',
            'distanceProgressPercent',
            'caloriesProgressPercent',
            'isDistanceAchieved',
            'isCaloriesAchieved',
            'isBothAchieved',
            'targetAchievedType',
            'sports',
            'weeklyHistory',
            'streakMilestone'
        ));
    }

    /**
     * Update Target Harian Jarak & Kalori
     */
    public function updateTarget(Request $request)
    {
        $request->validate([
            'daily_distance_km' => 'required|numeric|min:0.1|max:500',
            'daily_calories' => 'required|integer|min:10|max:10000',
        ]);

        $user = Auth::user();

        ActivityTarget::updateOrCreate(
            ['user_id' => $user->id],
            [
                'daily_distance_km' => $request->daily_distance_km,
                'daily_calories' => $request->daily_calories,
                'is_active' => true,
            ]
        );

        return redirect()->back()->with('success', 'Target harian berhasil diperbarui!');
    }
}