<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Activity;
use Illuminate\Support\Facades\DB;
use Exception;

class ArchiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Halaman Utama Arsip & Riwayat Aktivitas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Menerima parameter 'sport_type' atau 'sport' agar konsisten dengan tautan filter Blade
        $sportFilter = $request->input('sport_type', $request->input('sport', 'all'));

        $query = Activity::where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('start_time');

        if ($sportFilter !== 'all' && in_array($sportFilter, ['running', 'walking', 'cycling'])) {
            $query->where('sport_type', $sportFilter);
        }

        // Pertahankan query parameter saat berpindah halaman pagination
        $activities = $query->paginate(7)->appends(['sport_type' => $sportFilter]);

        // Ringkasan Total Statistik Seluruh Aktivitas Valid
        $totalDistanceKm = round(Activity::where('user_id', $user->id)->where('status', 'completed')->sum('distance_meters') / 1000, 2);
        $totalCalories = Activity::where('user_id', $user->id)->where('status', 'completed')->sum('calories_burned');
        $totalDurationSec = Activity::where('user_id', $user->id)->where('status', 'completed')->sum('duration_seconds');

        // KALKULASI JAM & MENIT AGAR BLADE TIDAK UNDEFINED VARIABLE (FIX BUG 500)
        $totalHours = floor($totalDurationSec / 3600);
        $totalMinutes = floor(($totalDurationSec % 3600) / 60);

        return view('archive.index', compact(
            'activities', 
            'sportFilter', 
            'totalDistanceKm', 
            'totalCalories', 
            'totalDurationSec',
            'totalHours',
            'totalMinutes'
        ));
    }

    /**
     * Fetch API Point Koordinat Rute untuk Modal Peta
     */
    public function getRoutePoints($id)
    {
        try {
            $activity = Activity::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$activity) {
                return response()->json(['status' => 'error', 'message' => 'Aktivitas tidak ditemukan.'], 404);
            }

            $rawPoints = DB::table('activity_points')
                ->where('activity_id', $activity->id)
                ->orderBy('recorded_at', 'asc')
                ->select('latitude', 'longitude', 'altitude')
                ->get();

            $points = $rawPoints->map(function ($pt) {
                return [
                    'latitude' => (float) $pt->latitude,
                    'longitude' => (float) $pt->longitude,
                    'altitude' => (float) ($pt->altitude ?? 0),
                ];
            });

            return response()->json([
                'status' => 'success',
                'activity' => $activity,
                'points' => $points
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data rute: ' . $e->getMessage()
            ], 500);
        }
    }
}