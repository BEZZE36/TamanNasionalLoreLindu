<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'transaction_id',
        'order_id',
        'snap_token',
        'payment_type',
        'payment_channel',
        'va_number',
        'payment_code',
        'qr_code_url',
        'gross_amount',
        'status',
        'paid_at',
        'expired_at',
        'snap_response',
        'notification_response',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'snap_response' => 'array',
            'notification_response' => 'array',
        ];
    }

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CHALLENGE = 'challenge';
    public const STATUS_DENY = 'deny';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Pembayaran',
        self::STATUS_SUCCESS => 'Berhasil',
        self::STATUS_FAILED => 'Gagal',
        self::STATUS_EXPIRED => 'Kedaluwarsa',
        self::STATUS_REFUNDED => 'Dikembalikan',
        self::STATUS_CHALLENGE => 'Dalam Review',
        self::STATUS_DENY => 'Ditolak',
    ];

    // Payment type labels
    public const PAYMENT_TYPES = [
        'cash' => 'Tunai',
        'bank_transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'gopay' => 'GoPay',
        'shopeepay' => 'ShopeePay',
        'cstore' => 'Minimarket',
        'credit_card' => 'Kartu Kredit',
        'other' => 'Lainnya',
        'free' => 'Gratis (Kupon)',
    ];

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status ?? '-';
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        if (!$this->payment_type) {
            return '-';
        }
        return self::PAYMENT_TYPES[$this->payment_type] ?? $this->payment_type;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_SUCCESS => 'green',
            self::STATUS_FAILED, self::STATUS_DENY => 'red',
            self::STATUS_EXPIRED => 'gray',
            self::STATUS_REFUNDED => 'purple',
            self::STATUS_CHALLENGE => 'orange',
            default => 'gray',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->gross_amount, 0, ',', '.');
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_DENY]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }
}
