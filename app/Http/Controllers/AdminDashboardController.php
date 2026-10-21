<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserProfile;
use App\UserSetting;
use App\Streak;
use App\ActivityTarget;
use App\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // 1. Total Pengguna (Termasuk Admin & Non-Admin)
        $totalUsers = User::count();

        // 2. Total Pengguna Selain Admin (Bagi pembagi rumus statistik)
        $nonAdminUsersCount = User::where('role', '!=', 'admin')->count();
        if ($nonAdminUsersCount < 1) {
            $nonAdminUsersCount = 1; // Mencegah error Division by Zero
        }

        // 3. Query Aktivitas Valid (Status Completed & Jarak >= 10 meter)
        $activitiesQuery = Activity::where('status', 'completed')
            ->where('distance_meters', '>=', 10);

        $totalRunning = (clone $activitiesQuery)->where('sport_type', 'running')->count();
        $totalWalking = (clone $activitiesQuery)->where('sport_type', 'walking')->count();
        $totalCycling = (clone $activitiesQuery)->where('sport_type', 'cycling')->count();
        $totalCompletedActivities = $totalRunning + $totalWalking + $totalCycling;

        // Batas Rentang Waktu Minggu Ini (Auto reset setiap Senin 00:00:00)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        // 4. Peak Days (Minggu Ini)
        $daysOfWeek = [
            'Monday'    => 'Sen',
            'Tuesday'   => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday'  => 'Kam',
            'Friday'    => 'Jum',
            'Saturday'  => 'Sab',
            'Sunday'    => 'Min'
        ];

        $dailyCountsRaw = (clone $activitiesQuery)
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->select(DB::raw('DAYNAME(start_time) as day_name'), DB::raw('COUNT(*) as total'))
            ->groupBy('day_name')
            ->pluck('total', 'day_name')
            ->toArray();

        $chartData = [];
        $maxPercent = 0;
        $peakDayName = 'Belum Ada Data';

        foreach ($daysOfWeek as $fullDay => $shortDay) {
            $count = $dailyCountsRaw[$fullDay] ?? 0;
            
            // Rumus: (Aktivitas * 5) / Jumlah Non-Admin User
            $calculatedValue = ($count * 5) / $nonAdminUsersCount;
            $percentage = min(100, round($calculatedValue, 1));

            if ($percentage > $maxPercent) {
                $maxPercent = $percentage;
                $peakDayName = $fullDay;
            }

            $chartData[] = [
                'full_day'   => $fullDay,
                'short_day'  => $shortDay,
                'count'      => $count,
                'percentage' => $percentage
            ];
        }

        $dayMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $peakDayTranslated = $dayMap[$peakDayName] ?? 'Belum Ada Data';

        // 5. Peak Times (Minggu Ini: Pagi, Sore, Malam)
        $peakTimesRaw = (clone $activitiesQuery)
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->select(
                DB::raw("
                    CASE 
                        WHEN HOUR(start_time) >= 5 AND HOUR(start_time) < 12 THEN 'Pagi'
                        WHEN HOUR(start_time) >= 12 AND HOUR(start_time) < 18 THEN 'Sore'
                        ELSE 'Malam'
                    END as time_slot
                "),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('time_slot')
            ->pluck('total', 'time_slot')
            ->toArray();

        $peakTimesData = [
            'Pagi' => [
                'label' => 'Pagi (05:00 - 11:59)',
                'count' => $peakTimesRaw['Pagi'] ?? 0,
                'percentage' => min(100, round((($peakTimesRaw['Pagi'] ?? 0) * 5) / $nonAdminUsersCount, 1))
            ],
            'Sore' => [
                'label' => 'Sore (12:00 - 17:59)',
                'count' => $peakTimesRaw['Sore'] ?? 0,
                'percentage' => min(100, round((($peakTimesRaw['Sore'] ?? 0) * 5) / $nonAdminUsersCount, 1))
            ],
            'Malam' => [
                'label' => 'Malam (18:00 - 04:59)',
                'count' => $peakTimesRaw['Malam'] ?? 0,
                'percentage' => min(100, round((($peakTimesRaw['Malam'] ?? 0) * 5) / $nonAdminUsersCount, 1))
            ],
        ];

        // 6. Akumulasi Histori Minggu Terlewati (Card Minggu 1, Card Minggu 2, dst.)
        $firstActivity = (clone $activitiesQuery)->orderBy('start_time', 'asc')->first();
        $weeklyHistory = [];

        if ($firstActivity) {
            $firstWeekStart = Carbon::parse($firstActivity->start_time)->startOfWeek();
            $currentWeekStart = Carbon::now()->startOfWeek();

            $weekIndex = 1;
            $iterWeek = clone $firstWeekStart;

            while ($iterWeek < $currentWeekStart) {
                $iterWeekEnd = (clone $iterWeek)->endOfWeek();

                $weekActivitiesCount = (clone $activitiesQuery)
                    ->whereBetween('start_time', [$iterWeek, $iterWeekEnd])
                    ->count();

                // Peak Days Khusus Minggu Histori Ini
                $wDailyCountsRaw = (clone $activitiesQuery)
                    ->whereBetween('start_time', [$iterWeek, $iterWeekEnd])
                    ->select(DB::raw('DAYNAME(start_time) as day_name'), DB::raw('COUNT(*) as total'))
                    ->groupBy('day_name')
                    ->pluck('total', 'day_name')
                    ->toArray();

                $wChartData = [];
                $wMaxPercent = 0;
                $wPeakDayName = 'Belum Ada Data';

                foreach ($daysOfWeek as $fullDay => $shortDay) {
                    $cnt = $wDailyCountsRaw[$fullDay] ?? 0;
                    $calcVal = ($cnt * 5) / $nonAdminUsersCount;
                    $pct = min(100, round($calcVal, 1));

                    if ($pct > $wMaxPercent) {
                        $wMaxPercent = $pct;
                        $wPeakDayName = $fullDay;
                    }

                    $wChartData[] = [
                        'full_day'   => $fullDay,
                        'short_day'  => $shortDay,
                        'count'      => $cnt,
                        'percentage' => $pct
                    ];
                }

                $topDay = $dayMap[$wPeakDayName] ?? '-';

                // Peak Times Khusus Minggu Histori Ini
                $wPeakTimesRaw = (clone $activitiesQuery)
                    ->whereBetween('start_time', [$iterWeek, $iterWeekEnd])
                    ->select(
                        DB::raw("
                            CASE 
                                WHEN HOUR(start_time) >= 5 AND HOUR(start_time) < 12 THEN 'Pagi'
                                WHEN HOUR(start_time) >= 12 AND HOUR(start_time) < 18 THEN 'Sore'
                                ELSE 'Malam'
                            END as time_slot
                        "),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('time_slot')
                    ->pluck('total', 'time_slot')
                    ->toArray();

                $wPeakTimesData = [
                    'Pagi' => [
                        'label' => 'Pagi (05:00 - 11:59)',
                        'count' => $wPeakTimesRaw['Pagi'] ?? 0,
                        'percentage' => min(100, round((($wPeakTimesRaw['Pagi'] ?? 0) * 5) / $nonAdminUsersCount, 1))
                    ],
                    'Sore' => [
                        'label' => 'Sore (12:00 - 17:59)',
                        'count' => $wPeakTimesRaw['Sore'] ?? 0,
                        'percentage' => min(100, round((($wPeakTimesRaw['Sore'] ?? 0) * 5) / $nonAdminUsersCount, 1))
                    ],
                    'Malam' => [
                        'label' => 'Malam (18:00 - 04:59)',
                        'count' => $wPeakTimesRaw['Malam'] ?? 0,
                        'percentage' => min(100, round((($wPeakTimesRaw['Malam'] ?? 0) * 5) / $nonAdminUsersCount, 1))
                    ],
                ];

                $weeklyHistory[] = [
                    'week_number'     => $weekIndex,
                    'start_date'      => $iterWeek->format('d M Y'),
                    'end_date'        => $iterWeekEnd->format('d M Y'),
                    'total_count'     => $weekActivitiesCount,
                    'peak_day'        => $topDay,
                    'chart_data'      => $wChartData,
                    'peak_times_data' => $wPeakTimesData
                ];

                $weekIndex++;
                $iterWeek->addWeek();
            }
        }

        // 7. List 5 User Terbaru
        $latestUsers = User::with('profile')->orderBy('id', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'nonAdminUsersCount',
            'totalCompletedActivities',
            'totalRunning',
            'totalWalking',
            'totalCycling',
            'chartData',
            'peakDayTranslated',
            'peakTimesData',
            'weeklyHistory',
            'latestUsers'
        ));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password admin minimal 8 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => 'admin',
            ]);

            $baseUsername = explode('@', $user->email)[0];
            $cleanBase = Str::slug($baseUsername);
            $username = ($cleanBase ?: 'admin') . '_' . rand(100, 999);

            UserProfile::create([
                'user_id' => $user->id,
                'name' => trim($request->name),
                'username' => $username,
                'weight_kg' => 60.00,
            ]);

            UserSetting::create([
                'user_id' => $user->id,
                'theme' => 'dark',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]);

            Streak::create([
                'user_id' => $user->id,
                'current_streak' => 0,
                'longest_streak' => 0,
            ]);

            ActivityTarget::create([
                'user_id' => $user->id,
                'daily_distance_km' => 5.00,
                'daily_calories' => 300,
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->back()->with('success_admin', 'Admin baru berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['admin_error' => 'Gagal menambah admin: ' . $e->getMessage()]);
        }
    }
}