<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityPoint extends Model
{
    // Mengaktifkan timestamp tanpa updated_at/created_at bawaan laravel
    public $timestamps = false;

    protected $fillable = [
        'activity_id', 'latitude', 'longitude', 'altitude', 'speed_ms', 'accuracy_m', 'recorded_at',
    ];

    protected $dates = [
        'recorded_at',
    ];

    /**
     * Relasi ke Activity (Belongs To)
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
}