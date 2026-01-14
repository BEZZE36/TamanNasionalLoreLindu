<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'notification_type',
        'bg_color',
        'text_color',
        'link_url',
        'link_text',
        'is_dismissible',
        'is_active',
        'priority',
        'start_at',
        'end_at',
        'created_by',
        // Advanced features
        'view_count',
        'click_count',
        'dismiss_count',
        'target_audience',
        'target_pages',
        'exclude_pages',
        'position',
        'show_days',
        'show_time_start',
        'show_time_end',
        'animation_type',
        // New features
        'image_path',
        'show_countdown',
        'countdown_label',
        'extra_buttons',
        'history_log',
    ];

    protected function casts(): array
    {
        return [
            'is_dismissible' => 'boolean',
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'target_pages' => 'array',
            'exclude_pages' => 'array',
            'show_days' => 'array',
            'show_time_start' => 'datetime:H:i',
            'show_time_end' => 'datetime:H:i',
            'show_countdown' => 'boolean',
            'extra_buttons' => 'array',
        ];
    }

    // Type constants
    public const TYPE_BANNER = 'banner';
    public const TYPE_FULLSCREEN = 'fullscreen';

    // Target audience constants
    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_GUEST = 'guest';
    public const AUDIENCE_AUTHENTICATED = 'authenticated';
    public const AUDIENCE_FIRST_VISIT = 'first_visit';

    // Position constants
    public const POSITION_TOP = 'top';
    public const POSITION_BOTTOM = 'bottom';
    public const POSITION_FLOATING = 'floating';

    // Animation constants
    public const ANIMATIONS = ['fade', 'slide', 'bounce', 'zoom', 'flip'];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }

    public function scopeBanner($query)
    {
        return $query->where('type', self::TYPE_BANNER);
    }

    public function scopeFullscreen($query)
    {
        return $query->where('type', self::TYPE_FULLSCREEN);
    }

    public function scopeForAudience($query)
    {
        return $query->where(function ($q) {
            $q->where('target_audience', self::AUDIENCE_ALL);

            if (Auth::check()) {
                $q->orWhere('target_audience', self::AUDIENCE_AUTHENTICATED);
            } else {
                $q->orWhere('target_audience', self::AUDIENCE_GUEST);
            }
        });
    }

    // Helpers
    public function isBanner(): bool
    {
        return $this->type === self::TYPE_BANNER;
    }

    public function isFullscreen(): bool
    {
        return $this->type === self::TYPE_FULLSCREEN;
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }

        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }

        // Check day of week if specified
        if ($this->show_days && !empty($this->show_days)) {
            if (!in_array($now->dayOfWeek, $this->show_days)) {
                return false;
            }
        }

        // Check time range if specified
        if ($this->show_time_start && $this->show_time_end) {
            $currentTime = $now->format('H:i');
            if ($currentTime < $this->show_time_start || $currentTime > $this->show_time_end) {
                return false;
            }
        }

        return true;
    }

    public function shouldShowForAudience(): bool
    {
        switch ($this->target_audience) {
            case self::AUDIENCE_GUEST:
                return !Auth::check();
            case self::AUDIENCE_AUTHENTICATED:
                return Auth::check();
            case self::AUDIENCE_ALL:
            default:
                return true;
        }
    }

    public function shouldShowOnPage(string $currentPage): bool
    {
        // If exclude pages is set and current page is in it, don't show
        if ($this->exclude_pages && in_array($currentPage, $this->exclude_pages)) {
            return false;
        }

        // If target pages is set, check if current page is in it
        if ($this->target_pages && !empty($this->target_pages)) {
            return in_array($currentPage, $this->target_pages);
        }

        // No targeting, show on all pages
        return true;
    }

    public function incrementView(): void
    {
        $this->increment('view_count');
    }

    public function incrementClick(): void
    {
        $this->increment('click_count');
    }

    public function incrementDismiss(): void
    {
        $this->increment('dismiss_count');
    }

    public function getClickRate(): float
    {
        if ($this->view_count === 0)
            return 0;
        return round(($this->click_count / $this->view_count) * 100, 2);
    }

    public function getDismissRate(): float
    {
        if ($this->view_count === 0)
            return 0;
        return round(($this->dismiss_count / $this->view_count) * 100, 2);
    }

    public function getAnimationClass(): string
    {
        $animations = [
            'fade' => 'animate-fadeIn',
            'slide' => 'animate-slideIn',
            'bounce' => 'animate-bounce',
            'zoom' => 'animate-zoomIn',
            'flip' => 'animate-flipIn',
        ];

        return $animations[$this->animation_type] ?? $animations['fade'];
    }

    public static function getPageOptions(): array
    {
        return [
            'home' => 'Beranda',
            'destinations' => 'Destinasi',
            'destinations.detail' => 'Detail Destinasi',
            'about' => 'Tentang',
            'flora' => 'Flora',
            'flora.detail' => 'Detail Flora',
            'fauna' => 'Fauna',
            'fauna.detail' => 'Detail Fauna',
            'gallery' => 'Galeri',
            'gallery.detail' => 'Detail Galeri',
            'news' => 'Berita/Blog',
            'news.detail' => 'Detail Berita',
            'notifications' => 'Notifikasi',
            'contact' => 'Kontak',
            'search' => 'Halaman Pencarian',
            'all' => '📍 Semua Halaman',
        ];
    }

    public static function getDayOptions(): array
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
    }
}
