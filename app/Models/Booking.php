<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $order_number
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $visit_date
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 */
class Booking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'status_label',
        'status_color',
        'formatted_total_amount',
        'formatted_visit_date',
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'destination_id',
        'visit_date',
        'visit_time',
        'leader_name',
        'leader_nik',
        'leader_phone',
        'leader_email',
        'leader_address',
        'total_visitors',
        'total_adults',
        'total_children',
        'total_seniors',
        'total_motorcycles',
        'total_cars',
        'total_buses',
        'special_requests',
        'subtotal',
        'service_fee',
        'discount',
        'discount_code',
        'total_amount',
        'status',
        'cancel_reason',
        'confirmed_at',
        'used_at',
        'cancelled_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'visit_time' => 'datetime',
            'subtotal' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'used_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_CASH = 'awaiting_cash';
    public const STATUS_PAID = 'paid';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_USED = 'used';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Pembayaran',
        self::STATUS_AWAITING_CASH => 'Menunggu Pembayaran Tunai',
        self::STATUS_PAID => 'Sudah Dibayar',
        self::STATUS_CONFIRMED => 'Dikonfirmasi',
        self::STATUS_USED => 'Sudah Digunakan',
        self::STATUS_CANCELLED => 'Dibatalkan',
        self::STATUS_EXPIRED => 'Kedaluwarsa',
        self::STATUS_REFUNDED => 'Dikembalikan',
    ];

    // Auto-generate order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->order_number)) {
                $booking->order_number = self::generateOrderNumber();
            }
            $booking->total_visitors = ($booking->total_adults ?? 0) + ($booking->total_children ?? 0) + ($booking->total_seniors ?? 0);
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'TNLL';
        $date = now()->format('Ymd');
        // Add timestamp (seconds) + random to ensure uniqueness even after migrate:fresh
        // Midtrans remembers old order_ids, so we need globally unique IDs
        $timestamp = now()->format('His'); // HH:mm:ss
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$date}-{$timestamp}{$random}";
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_AWAITING_CASH => 'orange',
            self::STATUS_PAID, self::STATUS_CONFIRMED => 'green',
            self::STATUS_USED => 'blue',
            self::STATUS_CANCELLED, self::STATUS_EXPIRED => 'red',
            self::STATUS_REFUNDED => 'purple',
            default => 'gray',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount ?? 0, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal ?? 0, 0, ',', '.');
    }

    public function getFormattedServiceFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->service_fee ?? 0, 0, ',', '.');
    }

    public function getFormattedVisitDateAttribute(): string
    {
        return $this->visit_date?->translatedFormat('l, d F Y') ?? '-';
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_CONFIRMED]);
    }

    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() && $this->visit_date?->isFuture();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', [self::STATUS_PAID, self::STATUS_CONFIRMED]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('visit_date', '>=', today())
            ->whereIn('status', [self::STATUS_PAID, self::STATUS_CONFIRMED]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', today());
    }
}

