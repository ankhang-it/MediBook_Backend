<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetOtp extends Model
{
    use HasFactory;

    protected $table = 'password_reset_otps';

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a 4-digit OTP
     */
    public static function generateOtp(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create or update OTP for email
     */
    public static function createOrUpdateOtp(string $email): self
    {
        // Mark all existing OTPs for this email as used
        self::where('email', $email)
            ->where('used', false)
            ->update(['used' => true]);

        // Create new OTP
        $otp = self::generateOtp();
        $expiresAt = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes

        return self::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'used' => false,
        ]);
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp(string $email, string $otp): bool
    {
        $otpRecord = self::where('email', $email)
            ->where('otp', $otp)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otpRecord) {
            // Mark as used
            $otpRecord->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP exists and is valid
     */
    public static function isValidOtp(string $email, string $otp): bool
    {
        return self::where('email', $email)
            ->where('otp', $otp)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }
}

