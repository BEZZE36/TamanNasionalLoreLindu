<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    public const GROUPS = [
        'general' => 'Umum',
        'contact' => 'Kontak',
        'social' => 'Media Sosial',
        'seo' => 'SEO',
        'booking' => 'Pemesanan',
        'email' => 'Email',
        'appearance' => 'Tampilan',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    // Cache key
    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 3600; // 1 hour

    // Alias for getValue (backward compatibility) - use getSetting to avoid conflict with Eloquent get()
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        return self::getValue($key, $default);
    }

    // Alias for setValue (backward compatibility) - use setSetting to avoid conflict
    public static function setSetting(string $key, mixed $value, ?string $type = null): void
    {
        self::setValue($key, $value, $type);
    }

    // Get a setting value
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = self::getAllCached();

        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = $settings[$key];
        return self::castValue($setting['value'], $setting['type']);
    }

    // Set a setting value
    public static function setValue(string $key, mixed $value, ?string $type = null): void
    {
        $setting = self::where('key', $key)->first();

        // Convert value for storage
        $storedValue = $value;
        if ($type === 'boolean' || is_bool($value)) {
            $storedValue = $value ? '1' : '0';
        } elseif (is_array($value)) {
            $storedValue = json_encode($value);
        }

        if ($setting) {
            $setting->update([
                'value' => $storedValue,
                'type' => $type ?? $setting->type,
            ]);
        } else {
            self::create([
                'key' => $key,
                'value' => $storedValue,
                'type' => $type ?? (is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : 'string')),
            ]);
        }

        self::clearCache();
    }

    // Get all settings cached
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()->keyBy('key')->map(function ($setting) {
                return [
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'is_public' => $setting->is_public,
                ];
            })->toArray();
        });
    }

    // Get public settings only
    public static function getPublic(): array
    {
        $settings = self::getAllCached();

        return collect($settings)
            ->filter(fn($s) => $s['is_public'])
            ->map(fn($s) => self::castValue($s['value'], $s['type']))
            ->toArray();
    }

    // Get settings by group
    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    // Clear cache
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // Cast value based on type
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json', 'array' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
