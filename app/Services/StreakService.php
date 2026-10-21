<?php

namespace App\Services;

use App\Streak;
use Carbon\Carbon;

class StreakService
{
    /**
     * Memperbarui Streak Harian Pengguna Saat Aktivitas Valid Selesai
     */
    public static function updateStreak(int $userId): void
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $userId],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_qualifying_date' => null]
        );

        $timezone = 'Asia/Jakarta';
        $today = Carbon::now($timezone)->toDateString();
        $yesterday = Carbon::yesterday($timezone)->toDateString();

        // Standardisasi format tanggal dari database
        $lastDate = $streak->last_qualifying_date 
            ? Carbon::parse($streak->last_qualifying_date, $timezone)->toDateString() 
            : null;

        if ($lastDate === $today) {
            // Sudah menambah streak hari ini
            return;
        }

        if ($lastDate === $yesterday) {
            // Melanjutkan streak dari kemarin
            $streak->current_streak += 1;
        } else {
            // Streak terputus atau baru pertama kali
            $streak->current_streak = 1;
        }

        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_qualifying_date = $today;
        $streak->save();
    }
}