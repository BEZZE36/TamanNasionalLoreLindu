<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'admin_id',
        'reply_message',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    // Relationships
    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Boot method to update feedback status
    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            if ($reply->is_public) {
                $reply->feedback->update(['status' => Feedback::STATUS_REPLIED]);
            }
        });
    }
}
