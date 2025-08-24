<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the OTP codes for the user.
     */
    public function otpCodes()
    {
        return $this->hasMany(OTPCode::class);
    }

    /**
     * Get the latest valid OTP code for the user.
     */
    public function getLatestValidOTP()
    {
        return $this->otpCodes()
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Mark user as verified.
     */
    public function markAsVerified()
    {
        $this->update(['is_verified' => true]);
    }
}
