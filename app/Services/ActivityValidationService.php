<?php

namespace App\Services;

use App\Activity;
use App\Services\StreakService;
use Carbon\Carbon;

class ActivityValidationService
{
    // Pengaturan minimal jarak (dalam satuan meter)
    const MIN_DISTANCE_METERS = 5.0; 

    public static function validateAndComplete(Activity $activity): array
    {
        $endTime = now();

        // Ambil data terbaru dari database hasil sync poin terakhir
        $activity->refresh();

        // Pastikan durasi terisi jika belum ada dari sync
        if ($activity->duration_seconds <= 0 && $activity->start_time) {
            $startTime = $activity->start_time instanceof Carbon 
                ? $activity->start_time 
                : Carbon::parse($activity->start_time);
            $activity->duration_seconds = max(0, $endTime->diffInSeconds($startTime));
        }

        // Cek syarat minimal jarak 5 Meter (0,005 KM)
        if ($activity->distance_meters >= self::MIN_DISTANCE_METERS) {
            $activity->update([
                'status' => 'completed',
                'end_time' => $endTime,
                'duration_seconds' => $activity->duration_seconds,
            ]);

            // Update Streak Harian User
            StreakService::updateStreak($activity->user_id);

            return [
                'isValid' => true,
                'message' => 'Aktivitas tuntas! Streak sampeyan bertambah.'
            ];
        } else {
            // Gunakan status 'cancelled' agar sesuai dengan ENUM MySQL (active, completed, cancelled)
            $activity->update([
                'status' => 'cancelled',
                'end_time' => $endTime,
                'duration_seconds' => $activity->duration_seconds,
            ]);

            return [
                'isValid' => false,
                'message' => 'Aktivitas gagal disimpan karena kurang dari 5 Meter.'
            ];
        }
    }
}