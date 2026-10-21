<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserProfile;
use App\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Search users by Name, Username, or Public ID
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $users = User::select('users.id', 'users.public_id', 'p.name', 'p.username', 'p.avatar', 'p.bio')
            ->join('user_profiles as p', 'users.id', '=', 'p.user_id')
            ->where(function ($q) use ($query) {
                $q->where('users.public_id', 'LIKE', "%{$query}%")
                  ->orWhere('p.username', 'LIKE', "%{$query}%")
                  ->orWhere('p.name', 'LIKE', "%{$query}%");
            })
            ->limit(8)
            ->get();

        $formatted = $users->map(function ($u) {
            return [
                'public_id' => $u->public_id,
                'name' => $u->name ?? 'Pengguna WOYuk!',
                'username' => $u->username ?? 'user',
                'avatar_url' => $u->avatar ? asset('storage/' . $u->avatar) : null,
                'bio' => $u->bio ? \Illuminate\Support\Str::limit($u->bio, 60) : 'Member WOYuk!',
                'profile_url' => route('user.profile.show', ['public_id' => $u->public_id])
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }

    /**
     * Tampilkan Halaman Profil Publik Pengguna Lain berdasarkan public_id (Dukungan Filter 1H, 7H, 30H)
     */
    public function showProfile(Request $request, $public_id)
    {
        $targetUser = User::where('public_id', $public_id)->firstOrFail();
        
        $profile = UserProfile::firstOrCreate(
            ['user_id' => $targetUser->id],
            ['name' => 'User ' . $targetUser->public_id]
        );

        $userSetting = DB::table('user_settings')->where('user_id', $targetUser->id)->first();
        $userTimezone = $userSetting->timezone ?? 'Asia/Jakarta';

        // 1. Total Sesi per Kategori & Total Jarak
        $stats = [
            'running' => Activity::where('user_id', $targetUser->id)->where('status', 'completed')->where('sport_type', 'running')->count(),
            'walking' => Activity::where('user_id', $targetUser->id)->where('status', 'completed')->where('sport_type', 'walking')->count(),
            'cycling' => Activity::where('user_id', $targetUser->id)->where('status', 'completed')->where('sport_type', 'cycling')->count(),
            'total_activities' => Activity::where('user_id', $targetUser->id)->where('status', 'completed')->count(),
            'total_distance' => round(Activity::where('user_id', $targetUser->id)->where('status', 'completed')->sum('distance_meters') / 1000, 2),
        ];

        // 2. Filter Rentang Waktu Grafik (1d = 1 Hari, 7d = 7 Hari, 30d = 30 Hari)
        $period = $request->get('period', '7d');

        $chartLabels = [];
        $runningData = [];
        $walkingData = [];
        $cyclingData = [];

        if ($period === '1d') {
            for ($i = 21; $i >= 0; $i -= 3) {
                $startTime = Carbon::now($userTimezone)->subHours($i + 3);
                $endTime = Carbon::now($userTimezone)->subHours($i);
                $chartLabels[] = $endTime->format('H:i');

                $runningData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'running')
                    ->whereBetween('start_time', [$startTime, $endTime])
                    ->sum('distance_meters') / 1000, 2);

                $walkingData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'walking')
                    ->whereBetween('start_time', [$startTime, $endTime])
                    ->sum('distance_meters') / 1000, 2);

                $cyclingData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'cycling')
                    ->whereBetween('start_time', [$startTime, $endTime])
                    ->sum('distance_meters') / 1000, 2);
            }
        } elseif ($period === '30d') {
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now($userTimezone)->subDays($i)->toDateString();
                $chartLabels[] = Carbon::parse($date)->translatedFormat('d M');

                $runningData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'running')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $walkingData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'walking')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $cyclingData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'cycling')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);
            }
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now($userTimezone)->subDays($i)->toDateString();
                $chartLabels[] = Carbon::parse($date)->translatedFormat('d M');

                $runningData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'running')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $walkingData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'walking')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);

                $cyclingData[] = round(Activity::where('user_id', $targetUser->id)
                    ->where('status', 'completed')
                    ->where('sport_type', 'cycling')
                    ->whereDate('start_time', $date)
                    ->sum('distance_meters') / 1000, 2);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'chartLabels' => $chartLabels,
                'runningData' => $runningData,
                'walkingData' => $walkingData,
                'cyclingData' => $cyclingData,
            ]);
        }

        return view('profile.public_show', compact(
            'targetUser',
            'profile',
            'stats',
            'chartLabels',
            'runningData',
            'walkingData',
            'cyclingData',
            'period'
        ));
    }
}