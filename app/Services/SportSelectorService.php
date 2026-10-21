<?php

namespace App\Services;

class SportSelectorService
{
    /**
     * Konfigurasi & Aturan Khusus tiap Jenis Olahraga
     */
    protected static $sports = [
        'running' => [
            'key' => 'running',
            'name' => 'Lari',
            'icon' => 'running',
            'primary_metric' => 'pace', // Pace MM:SS /km
            'met_value' => 9.8,        // MET Value untuk estimasi kalori
            'max_valid_speed_kmh' => 35.0, // Batas atas kecepatan realistis
        ],
        'walking' => [
            'key' => 'walking',
            'name' => 'Jalan',
            'icon' => 'walking',
            'primary_metric' => 'pace',
            'met_value' => 3.8,
            'max_valid_speed_kmh' => 15.0,
        ],
        'cycling' => [
            'key' => 'cycling',
            'name' => 'Bersepeda',
            'icon' => 'cycling',
            'primary_metric' => 'speed', // Speed KM/H
            'met_value' => 7.5,
            'max_valid_speed_kmh' => 100.0,
        ],
    ];

    /**
     * Memvalidasi apakah jenis olahraga valid
     */
    public static function isValid(string $sportType): bool
    {
        return array_key_exists($sportType, self::$sports);
    }

    /**
     * Mengambil konfigurasi jenis olahraga
     */
    public static function getConfig(string $sportType): array
    {
        return self::$sports[$sportType] ?? self::$sports['running'];
    }

    /**
     * Mengambil seluruh konfigurasi jenis olahraga (Dibutuhkan oleh Dashboard)
     */
    public static function getAllConfigs(): array
    {
        return self::$sports;
    }

    /**
     * Alias untuk mengambil seluruh konfigurasi jenis olahraga
     */
    public static function getAll(): array
    {
        return self::$sports;
    }
}