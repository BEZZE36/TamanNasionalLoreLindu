<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'is_active',
        'description',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // Available templates
    const TEMPLATES = [
        'booking_confirmation' => 'Konfirmasi Pemesanan',
        'payment_success' => 'Pembayaran Berhasil',
        'payment_reminder' => 'Reminder Pembayaran',
        'ticket_issued' => 'E-Ticket Diterbitkan',
        'booking_cancelled' => 'Pemesanan Dibatalkan',
        'welcome' => 'Selamat Datang',
        'password_reset' => 'Reset Password',
        'contact_reply' => 'Balasan Kontak',
    ];

    // Default variables per template
    const DEFAULT_VARIABLES = [
        'booking_confirmation' => ['name', 'order_number', 'destination', 'visit_date', 'total_amount'],
        'payment_success' => ['name', 'order_number', 'amount', 'payment_method', 'transaction_id'],
        'payment_reminder' => ['name', 'order_number', 'amount', 'due_date'],
        'ticket_issued' => ['name', 'order_number', 'destination', 'visit_date', 'ticket_code'],
        'booking_cancelled' => ['name', 'order_number', 'reason'],
        'welcome' => ['name', 'email'],
        'password_reset' => ['name', 'reset_link'],
        'contact_reply' => ['name', 'subject', 'message'],
    ];

    // Render template with variables
    public function render(array $data): string
    {
        $body = $this->body;
        
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
            $body = str_replace('{{ ' . $key . ' }}', $value, $body);
        }
        
        return $body;
    }

    // Get template by slug
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->where('is_active', true)->first();
    }

    // Preview with sample data
    public function preview(): string
    {
        $sampleData = [];
        $variables = $this->variables ?? self::DEFAULT_VARIABLES[$this->slug] ?? [];
        
        foreach ($variables as $variable) {
            $sampleData[$variable] = "[Sample {$variable}]";
        }
        
        return $this->render($sampleData);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
