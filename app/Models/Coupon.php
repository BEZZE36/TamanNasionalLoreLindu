<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'start_date',
        'end_date',
        'is_active',
        'applicable_destinations',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'applicable_destinations' => 'array',
    ];

    // Discount types
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';

    const TYPES = [
        self::TYPE_PERCENTAGE => 'Persentase (%)',
        self::TYPE_FIXED => 'Nominal Tetap (Rp)',
    ];

    // Relationships
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
            });
    }

    // Check if coupon is valid
    public function isValid(?int $userId = null, ?float $orderAmount = null, ?int $destinationId = null): array
    {
        // Check active
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Kupon tidak aktif.'];
        }

        // Check dates
        $now = now();
        if ($this->start_date && $this->start_date > $now) {
            return ['valid' => false, 'message' => 'Kupon belum berlaku.'];
        }
        if ($this->end_date && $this->end_date < $now) {
            return ['valid' => false, 'message' => 'Kupon sudah kadaluarsa.'];
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Kupon sudah mencapai batas penggunaan.'];
        }

        // Check per user limit
        if ($userId && $this->per_user_limit) {
            $userUsage = $this->usages()->where('user_id', $userId)->count();
            if ($userUsage >= $this->per_user_limit) {
                return ['valid' => false, 'message' => 'Anda sudah mencapai batas penggunaan kupon ini.'];
            }
        }

        // Check minimum order
        if ($orderAmount && $this->min_order_amount && $orderAmount < $this->min_order_amount) {
            return [
                'valid' => false, 
                'message' => 'Minimum pembelian Rp ' . number_format($this->min_order_amount, 0, ',', '.') . '.'
            ];
        }

        // Check applicable destinations
        if ($destinationId && $this->applicable_destinations && count($this->applicable_destinations) > 0) {
            if (!in_array($destinationId, $this->applicable_destinations)) {
                return ['valid' => false, 'message' => 'Kupon tidak berlaku untuk destinasi ini.'];
            }
        }

        return ['valid' => true, 'message' => 'Kupon valid.'];
    }

    // Calculate discount
    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = $amount * ($this->discount_value / 100);
        } else {
            $discount = $this->discount_value;
        }

        // Apply max discount cap
        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        // Discount cannot exceed order amount
        if ($discount > $amount) {
            $discount = $amount;
        }

        return $discount;
    }

    // Mark as used
    public function markAsUsed(int $userId, int $bookingId): void
    {
        $this->increment('used_count');
        
        CouponUsage::create([
            'coupon_id' => $this->id,
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'used_at' => now(),
        ]);
    }

    // Accessors
    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return $this->discount_value . '%';
        }
        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        $validation = $this->isValid();
        if (!$validation['valid']) {
            return $validation['message'];
        }
        return 'Aktif';
    }

    // Static helper
    public static function findByCode(string $code): ?self
    {
        return self::where('code', strtoupper(trim($code)))->first();
    }
}
