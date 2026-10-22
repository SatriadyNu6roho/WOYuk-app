<?php

namespace App\Services;

use App\Streak;
use Carbon\Carbon;

class StreakService
{
    /**
     * Memperbarui Streak Harian Pengguna Saat Aktivitas Valid Selesai
     * 
     * @return int|null Mengembalikan angka milestone (10, 50, 100) jika baru tercapai hari ini, atau null.
     */
    public static function updateStreak(int $userId): ?int
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $userId],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_qualifying_date' => null]
        );

        $timezone = 'Asia/Jakarta';
        $today = Carbon::now($timezone)->toDateString();
        $yesterday = Carbon::yesterday($timezone)->toDateString();
        $twoDaysAgo = Carbon::now($timezone)->subDays(2)->toDateString();

        // Standardisasi format tanggal dari database
        $lastDate = $streak->last_qualifying_date 
            ? Carbon::parse($streak->last_qualifying_date, $timezone)->toDateString() 
            : null;

        if ($lastDate === $today) {
            // Sudah menambah streak hari ini, tidak perlu melakukan apa-apa
            return null;
        }

        if ($lastDate === $yesterday) {
            // Normal: Melanjutkan streak dari kemarin
            $streak->current_streak += 1;
        } elseif ($lastDate === $twoDaysAgo) {
            // BANGUN DARI TIDUR: Bolos 1 hari kemarin, tapi hari ini olahraga. Streak TIDAK hangus.
            $streak->current_streak += 1;
        } else {
            // HANGUS: Sudah bolos lebih dari 1 hari, streak reset ke 1
            $streak->current_streak = 1;
        }

        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_qualifying_date = $today;
        $streak->save();

        // Cek apakah current_streak saat ini adalah angka milestone
        $milestones = [10, 50, 100];
        if (in_array($streak->current_streak, $milestones)) {
            return (int) $streak->current_streak; // Kembalikan angka milestone
        }

        return null;
    }
}