<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    protected $fillable = [
        'user_id', 'current_streak', 'longest_streak', 'last_qualifying_date',
    ];

    protected $dates = [
        'last_qualifying_date',
    ];

    /**
     * Relasi ke User (Belongs To)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}