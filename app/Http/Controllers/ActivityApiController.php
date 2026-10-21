<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Activity;
use App\ActivityPoint;
use App\Services\ActivityMetricsService;
use Illuminate\Support\Facades\DB;
use Exception;

class ActivityApiController extends Controller
{
    /**
     * Endpoint Menerima Batch Koordinat GPS Realtime dari Browser
     */
    public function syncPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'nullable|array',
            'total_distance_meters' => 'required|numeric',
            'duration_seconds' => 'required|integer',
        ]);

        $user = auth()->user()->load('profile');
        $activity = Activity::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$activity) {
            return response()->json(['status' => 'error', 'message' => 'Aktivitas ora ditemokake.'], 404);
        }

        DB::beginTransaction();
        try {
            $insertData = [];
            if ($request->has('points') && is_array($request->points)) {
                foreach ($request->points as $pt) {
                    if (isset($pt['latitude']) && isset($pt['longitude'])) {
                        $insertData[] = [
                            'activity_id' => $activity->id,
                            'latitude' => $pt['latitude'],
                            'longitude' => $pt['longitude'],
                            'altitude' => $pt['altitude'] ?? 0,
                            'speed_ms' => $pt['speed'] ?? 0,
                            'accuracy_m' => $pt['accuracy'] ?? 0,
                            'recorded_at' => isset($pt['timestamp']) ? date('Y-m-d H:i:s', strtotime($pt['timestamp'])) : now(),
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                ActivityPoint::insert($insertData);
            }

            // SAFE NAVIGATION NULL CHECK UNTUK PROFILE
            $weightKg = optional($user->profile)->weight_kg ?? 65.0;
            $totalMeters = $request->total_distance_meters;

            // AKURASI KALORI: Jika jarak masih di bawah 5 meter (diam/istirahat), gunakan Resting BMR MET
            if ($totalMeters < 5.0) {
                $caloriesBurned = round((1.2 * $weightKg * ($request->duration_seconds / 3600)), 0);
            } else {
                $caloriesBurned = ActivityMetricsService::calculateCalories(
                    $activity->sport_type,
                    $request->duration_seconds,
                    $weightKg
                );
            }

            // Hitung Elevasi Tambahan
            $newElevationGain = ActivityMetricsService::calculateElevationGain($request->points ?? []);
            $totalElevation = $activity->elevation_gain_m + $newElevationGain;

            // Update Ringkasan Aktivitas
            $avgSpeedKmh = $request->duration_seconds > 0 ? ($totalMeters / 1000) / ($request->duration_seconds / 3600) : 0;
            
            $avgPaceStr = "00:00";
            if ($totalMeters > 0) {
                $paceSecondsPerKm = $request->duration_seconds / ($totalMeters / 1000);
                $avgPaceStr = sprintf("%02d:%02d", floor($paceSecondsPerKm / 60), floor($paceSecondsPerKm % 60));
            }

            $activity->update([
                'distance_meters' => $totalMeters,
                'duration_seconds' => $request->duration_seconds,
                'avg_speed_kmh' => round($avgSpeedKmh, 2),
                'avg_pace_min_km' => $avgPaceStr,
                'calories_burned' => $caloriesBurned,
                'elevation_gain_m' => $totalElevation,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'calories_burned' => $caloriesBurned,
                'elevation_gain_m' => $totalElevation
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint API Ambil Seluruh Koordinat Rute & Detail Metrik Lengkap untuk Canvas
     */
    public function getRoutePoints($id)
    {
        try {
            $activity = Activity::with(['user.profile'])
                ->where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$activity) {
                return response()->json(['status' => 'error', 'message' => 'Aktivitas tidak ditemukan.'], 404);
            }

            // Gunakan Query Builder langsung untuk menghindari error Carbon/DateTime 'Trailing data'
            $rawPoints = DB::table('activity_points')
                ->where('activity_id', $activity->id)
                ->orderBy('recorded_at', 'asc')
                ->select('latitude', 'longitude', 'altitude', 'recorded_at')
                ->get();

            $points = $rawPoints->map(function ($pt) {
                return [
                    'latitude' => (float) $pt->latitude,
                    'longitude' => (float) $pt->longitude,
                    'altitude' => (float) ($pt->altitude ?? 0),
                    'recorded_at' => (string) $pt->recorded_at,
                ];
            });

            $distanceMeters = (float) ($activity->distance_meters ?? 0);
            $durationSeconds = max(0, (int) ($activity->duration_seconds ?? 0));

            // Hitung kalkulasi Pace Min:Sec aman tanpa division by zero
            $pMin = '00';
            $pSec = '00';
            if ($distanceMeters > 0) {
                $paceSec = $durationSeconds / ($distanceMeters / 1000);
                $pMin = str_pad(floor($paceSec / 60), 2, '0', STR_PAD_LEFT);
                $pSec = str_pad(floor($paceSec % 60), 2, '0', STR_PAD_LEFT);
            }

            // SAFE NAVIGATION NULL CHECK UNTUK USER PROFILE NAME
            $userName = optional($activity->user->profile)->name ?? $activity->user->name ?? 'Atlet WOYuk!';

            return response()->json([
                'status' => 'success',
                'activity' => [
                    'id' => $activity->id,
                    'sport_type' => strtolower($activity->sport_type ?? 'running'),
                    'sport_name' => strtoupper($activity->sport_type ?? 'RUNNING'),
                    'distance_meters' => $distanceMeters,
                    'distance_km' => number_format($distanceMeters / 1000, 2, ',', '.'),
                    'duration_seconds' => $durationSeconds,
                    'duration_str' => gmdate("H:i:s", $durationSeconds),
                    'calories' => (int) ($activity->calories_burned ?? 0),
                    'avg_speed_kmh' => (float) ($activity->avg_speed_kmh ?? 0),
                    'pace_min' => $pMin,
                    'pace_sec' => $pSec,
                    'user_name' => $userName,
                    'created_at_formatted' => \Carbon\Carbon::parse($activity->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i'),
                ],
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