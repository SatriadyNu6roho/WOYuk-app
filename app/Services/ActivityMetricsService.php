<?php

namespace App\Services;

use App\Services\SportSelectorService;

class ActivityMetricsService
{
    /**
     * Hitung Estimasi Kalori Terbakar (Kcal)
     */
    public static function calculateCalories(string $sportType, int $durationSeconds, float $userWeightKg = 65.0): int
    {
        $sportConfig = SportSelectorService::getConfig($sportType);
        $met = $sportConfig['met_value'] ?? 5.0;

        $durationHours = $durationSeconds / 3600;
        $calories = $met * $userWeightKg * $durationHours;

        return (int) round($calories);
    }

    /**
     * Hitung Akumulasi Elevation Gain (Meter) dari Array Koordinat
     */
    public static function calculateElevationGain(array $points): float
    {
        $totalGain = 0.0;
        $prevAltitude = null;

        foreach ($points as $pt) {
            $currentAlt = floatval($pt['altitude'] ?? 0);

            if ($prevAltitude !== null && $currentAlt > $prevAltitude) {
                $gain = $currentAlt - $prevAltitude;
                // Filter noise elevasi kecil (< 0.5m)
                if ($gain >= 0.5) {
                    $totalGain += $gain;
                }
            }
            $prevAltitude = $currentAlt;
        }

        return round($totalGain, 2);
    }
}