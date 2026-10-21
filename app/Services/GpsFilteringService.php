<?php

namespace App\Services;

class GpsFilteringService
{
    /**
     * Memeriksa apakah satu titik GPS layak dimasukkan ke perhitungan rute
     */
    public static function isValidPoint(array $currentPoint, ?array $previousPoint, string $sportType): bool
    {
        // 1. Akurasi Sinyal GPS (Filter Akurasi Buruk > 30m)
        $accuracy = $currentPoint['accuracy'] ?? 999;
        if ($accuracy > 30.0) {
            return false;
        }

        // Jika belum ada titik sebelumnya, titik pertama langsung valid
        if (!$previousPoint) {
            return true;
        }

        // 2. Filter Duplikat Koordinat
        if ($currentPoint['latitude'] == $previousPoint['latitude'] && 
            $currentPoint['longitude'] == $previousPoint['longitude']) {
            return false;
        }

        // 3. Hitung Jarak & Kecepatan Antar 2 Titik
        $distanceMeters = self::calculateHaversine(
            $previousPoint['latitude'], $previousPoint['longitude'],
            $currentPoint['latitude'], $currentPoint['longitude']
        );

        // Jika perpindahan kurang dari 1.5 meter, abaikan (Stationary Drift Filter)
        if ($distanceMeters < 1.5) {
            return false;
        }

        // Hitung Selisih Waktu (Detik)
        $time1 = strtotime($previousPoint['timestamp']);
        $time2 = strtotime($currentPoint['timestamp']);
        $timeDiffSeconds = max(1, abs($time2 - $time1));

        // Kecepatan Instan (km/h)
        $speedKmh = ($distanceMeters / 1000) / ($timeDiffSeconds / 3600);

        // 4. Impossible Speed Jump Filter
        $maxSpeedAllowed = ($sportType === 'cycling') ? 100.0 : 35.0;
        if ($speedKmh > $maxSpeedAllowed) {
            return false;
        }

        return true;
    }

    /**
     * Perhitungan Haversine Formula (Meter)
     */
    public static function calculateHaversine($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}