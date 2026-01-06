<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $ticket_code
 * @property \Illuminate\Support\Carbon|null $valid_date
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property string $status
 * @property int $booking_id
 * @property \App\Models\Booking $booking
 */
class Ticket extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'status_label',
        'status_color',
        'qr_code_url',
    ];

    protected $fillable = [
        'booking_id',
        'ticket_code',
        'qr_code_path',
        'qr_data',
        'valid_date',
        'total_persons',
        'status',
        'used_at',
        'validated_by',
        'validation_notes',
        'download_count',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_date' => 'date',
            'used_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
        ];
    }

    // Status constants
    public const STATUS_VALID = 'valid';
    public const STATUS_USED = 'used';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_VALID => 'Valid',
        self::STATUS_USED => 'Sudah Digunakan',
        self::STATUS_EXPIRED => 'Kedaluwarsa',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    // Auto-generate ticket code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_code)) {
                $ticket->ticket_code = self::generateTicketCode();
            }
        });
    }

    public static function generateTicketCode(): string
    {
        $prefix = 'TKT';
        $random = strtoupper(Str::random(10));
        return "{$prefix}-{$random}";
    }

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'validated_by');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VALID => 'green',
            self::STATUS_USED => 'blue',
            self::STATUS_EXPIRED => 'gray',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public function getQrCodeUrlAttribute(): string
    {
        return $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : '';
    }

    // Status checks
    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID && $this->valid_date->isToday();
    }

    public function isValidForDate(): bool
    {
        return $this->status === self::STATUS_VALID && !$this->valid_date->isPast();
    }

    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || $this->valid_date->isPast();
    }

    public function canBeUsed(): bool
    {
        return $this->status === self::STATUS_VALID && $this->valid_date->isToday();
    }

    // Actions
    public function markAsUsed(Admin $admin, ?string $notes = null): bool
    {
        if (!$this->canBeUsed()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_USED,
            'used_at' => now(),
            'validated_by' => $admin->id,
            'validation_notes' => $notes,
        ]);

        // Update booking status
        $this->booking->update([
            'status' => Booking::STATUS_USED,
            'used_at' => now(),
        ]);

        return true;
    }

    public function incrementDownload(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', self::STATUS_VALID);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('valid_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('valid_date', '>=', today())
            ->where('status', self::STATUS_VALID);
    }
}
