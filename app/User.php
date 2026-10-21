<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'public_id', 'role', 'email', 'password', 'google_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Event Booting Model Laravel
     * Mengotomatiskan pembuatan Public User ID 10-Digit
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Format ID internal menjadi 10 digit dengan pad angka nol di depan
            // Contoh: id 1 -> 0000000001, id 25 -> 0000000025
            $user->public_id = str_pad($user->id, 10, '0', STR_PAD_LEFT);
            $user->save();
        });
    }

    /**
     * Relasi ke UserProfile (1 to 1)
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * Relasi ke UserSetting (1 to 1)
     */
    public function setting()
    {
        return $this->hasOne(UserSetting::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Aktivitas Olahraga (1 to Many)
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Streak (1 to 1)
     */
    public function streak()
    {
        return $this->hasOne(Streak::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Target Harian Aktif (1 to 1)
     */
    public function activeTarget()
    {
        return $this->hasOne(ActivityTarget::class, 'user_id', 'id')->where('is_active', true);
    }

    /**
     * Relasi ke Riwayat Target Harian (1 to Many)
     */
    public function targets()
    {
        return $this->hasMany(ActivityTarget::class, 'user_id', 'id');
    }
}