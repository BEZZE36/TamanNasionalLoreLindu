<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'otp_code',
        'type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // OTP types
    public const TYPE_REGISTER = 'register';
    public const TYPE_RESET = 'reset';

    // OTP expiry in seconds
    public const EXPIRY_SECONDS = 60;

    // Progressive cooldown in seconds [attempt => cooldown]
    // 1st resend: 60s, 2nd: 5 min, 3rd: 15 min, 4th+: 1 hour
    public const COOLDOWN_LEVELS = [
        1 => 60,      // First resend: 1 minute
        2 => 300,     // Second resend: 5 minutes
        3 => 900,     // Third resend: 15 minutes
        4 => 3600,    // Fourth+ resend: 1 hour
    ];

    /**
     * Get cooldown for given attempt number.
     */
    public static function getCooldownForAttempt(int $attempt): int
    {
        if ($attempt <= 0)
            return 0;
        if ($attempt >= 4)
            return self::COOLDOWN_LEVELS[4];
        return self::COOLDOWN_LEVELS[$attempt] ?? self::COOLDOWN_LEVELS[4];
    }

    /**
     * Generate a new 6-digit OTP for the given email and type.
     */
    public static function generateOtp(string $email, string $type = self::TYPE_REGISTER): self
    {
        // Delete any existing OTPs for this email and type
        self::where('email', $email)->where('type', $type)->delete();

        return self::create([
            'email' => $email,
            'otp_code' => self::generateCode(),
            'type' => $type,
            'expires_at' => now()->addSeconds(self::EXPIRY_SECONDS),
        ]);
    }

    /**
     * Generate a random 6-digit code.
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify OTP code for given email and type.
     */
    public static function verifyOtp(string $email, string $code, string $type = self::TYPE_REGISTER): bool
    {
        $otp = self::where('email', $email)
            ->where('type', $type)
            ->where('otp_code', $code)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            // Delete the OTP after successful verification
            $otp->delete();
            return true;
        }

        return false;
    }

    /**
     * Check if user can request a new OTP (progressive cooldown check).
     * Uses database to count OTPs created in last 24 hours.
     */
    public static function canResend(string $email, string $type = self::TYPE_REGISTER): array
    {
        // Count OTPs sent in the last 24 hours for this email
        $recentOtps = self::where('email', $email)
            ->where('type', $type)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        // Get the last OTP
        $lastOtp = self::where('email', $email)
            ->where('type', $type)
            ->latest()
            ->first();

        // If no previous OTP, can send immediately
        if (!$lastOtp) {
            return ['can_resend' => true, 'wait_seconds' => 0, 'attempt' => 1];
        }

        // Get cooldown based on number of OTPs sent in last 24 hours
        $cooldown = self::getCooldownForAttempt($recentOtps);
        $secondsSinceLastOtp = (int) now()->diffInSeconds($lastOtp->created_at);
        $waitSeconds = max(0, $cooldown - $secondsSinceLastOtp);

        \Log::info('Cooldown check', [
            'email' => $email,
            'recent_otps' => $recentOtps,
            'cooldown_required' => $cooldown,
            'seconds_since_last' => $secondsSinceLastOtp,
            'wait_seconds' => $waitSeconds,
        ]);

        return [
            'can_resend' => $waitSeconds === 0,
            'wait_seconds' => (int) $waitSeconds,
            'attempt' => $recentOtps + 1,
            'cooldown_used' => $cooldown,
        ];
    }

    /**
     * Note: recordAttempt no longer needed since we track via database.
     * Kept for backwards compatibility but does nothing useful now.
     */
    public static function recordAttempt(string $email, string $type = self::TYPE_REGISTER): int
    {
        // Just count OTPs - the generate function already creates record
        return self::where('email', $email)
            ->where('type', $type)
            ->where('created_at', '>', now()->subHours(24))
            ->count();
    }

    /**
     * Clear attempt count - not needed with database tracking.
     */
    public static function clearAttempts(string $email, string $type = self::TYPE_REGISTER): void
    {
        // No-op - database based tracking doesn't need clearing
    }

    /**
     * Check if this OTP is still valid.
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Get remaining seconds until expiry.
     */
    public function getRemainingSecondsAttribute(): int
    {
        if (!$this->isValid()) {
            return 0;
        }
        return now()->diffInSeconds($this->expires_at);
    }

    /**
     * Mask email for display (e.g., j***n@gmail.com).
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = $name[0] . str_repeat('*', strlen($name) - 2) . substr($name, -1);
        }

        return $masked . '@' . $domain;
    }

    /**
     * Mask phone number for display (e.g., 081***890).
     */
    public static function maskPhone(?string $phone): ?string
    {
        if (!$phone || strlen($phone) < 6) {
            return $phone;
        }

        $visible = 3;
        $start = substr($phone, 0, $visible);
        $end = substr($phone, -$visible);
        $middle = str_repeat('*', max(0, strlen($phone) - ($visible * 2)));

        return $start . $middle . $end;
    }

    /**
     * Mask username for display (e.g., joh***oe).
     */
    public static function maskUsername(string $username): string
    {
        if (strlen($username) <= 4) {
            return $username[0] . str_repeat('*', strlen($username) - 1);
        }

        $visible = 3;
        $start = substr($username, 0, $visible);
        $end = substr($username, -$visible);
        $middle = str_repeat('*', max(0, strlen($username) - ($visible * 2)));

        return $start . $middle . $end;
    }
}
