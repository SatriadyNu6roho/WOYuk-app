<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityTarget extends Model
{
    protected $fillable = [
        'user_id', 'daily_distance_km', 'daily_calories', 'is_active',
    ];

    /**
     * Relasi ke User (Belongs To)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}