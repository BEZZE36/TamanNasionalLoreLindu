<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_id',
        'category',
        'label',
        'description',
        'price',
        'weekend_price',
        'min_age',
        'max_age',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Category labels
    public const CATEGORIES = [
        'dewasa' => 'Dewasa (12+ tahun)',
        'anak' => 'Anak-anak (3-11 tahun)',
        'lansia' => 'Lansia (60+ tahun)',
        'kendaraan_motor' => 'Parkir Motor',
        'kendaraan_mobil' => 'Parkir Mobil',
        'kendaraan_bus' => 'Parkir Bus',
    ];

    // Relationships
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // Helpers
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function isVisitorCategory(): bool
    {
        return in_array($this->category, ['dewasa', 'anak', 'lansia']);
    }

    public function isVehicleCategory(): bool
    {
        return str_starts_with($this->category, 'kendaraan_');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVisitors($query)
    {
        return $query->whereIn('category', ['dewasa', 'anak', 'lansia']);
    }

    public function scopeVehicles($query)
    {
        return $query->where('category', 'like', 'kendaraan_%');
    }
}
