<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'sport_type', 'status', 'start_time', 'end_time',
        'duration_seconds', 'distance_meters', 'avg_speed_kmh',
        'avg_pace_min_km', 'elevation_gain_m', 'calories_burned',
    ];

    protected $dates = [
        'start_time', 'end_time',
    ];

    /**
     * Relasi ke User (Belongs To)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke ActivityPoints / Koordinat GPS (1 to Many)
     */
    public function points()
    {
        return $this->hasMany(ActivityPoint::class, 'activity_id', 'id')->orderBy('recorded_at', 'asc');
    }
}