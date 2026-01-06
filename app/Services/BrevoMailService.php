<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.brevo.com/v3/smtp/email';
    protected $sender = ['name' => 'TNLL Explore', 'email' => 'noreply@tnllexplore.com'];

    public function __construct()
    {
        $this->apiKey = env('BREVO_API_KEY');
    }

    /**
     * Send Booking Confirmation Email with PDF Ticket
     */
    public function sendBookingConfirmation(Booking $booking, $pdfContent)
    {
        if (!$this->apiKey) {
            Log::warning('Brevo API Key not found. Email not sent for Booking #' . $booking->order_number);
            return false;
        }

        $visitDate = $booking->visit_date->translatedFormat('d F Y');
        $totalAmount = number_format($booking->total_amount, 0, ',', '.');
        
        $htmlContent = "
            <html>
            <body>
                <h1>Pemesanan Berhasil!</h1>
                <p>Halo <strong>{$booking->leader_name}</strong>,</p>
                <p>Terima kasih telah memesan tiket masuk Taman Nasional Lore Lindu melalui TNLL Explore.</p>
                <p>Berikut adalah detail pesanan Anda:</p>
                <ul>
                    <li><strong>Nomor Pesanan:</strong> #{$booking->order_number}</li>
                    <li><strong>Destinasi:</strong> {$booking->destination->name}</li>
                    <li><strong>Tanggal Kunjungan:</strong> {$visitDate}</li>
                    <li><strong>Total Pengunjung:</strong> {$booking->total_visitors} Orang</li>
                    <li><strong>Total Pembayaran:</strong> Rp {$totalAmount}</li>
                </ul>
                <p>E-Ticket Anda terlampir dalam email ini. Silakan tunjukkan QR Code pada tiket saat masuk.</p>
                <br>
                <p>Selamat berwisata!</p>
                <p>Tim TNLL Explore</p>
            </body>
            </html>
        ";

        // Convert raw PDF content to base64 for attachment
        $base64Pdf = base64_encode($pdfContent);

        $payload = [
            'sender' => $this->sender,
            'to' => [
                ['email' => $booking->leader_email, 'name' => $booking->leader_name]
            ],
            'subject' => "E-Ticket TNLL Explore - #{$booking->order_number}",
            'htmlContent' => $htmlContent,
            'attachment' => [
                [
                    'content' => $base64Pdf,
                    'name' => "E-Ticket-{$booking->order_number}.pdf"
                ]
            ]
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Send Cash Payment Instructions
     */
    public function sendCashPaymentInstructions(Booking $booking)
    {
        if (!$this->apiKey) {
            return false;
        }

        $expiredAt = $booking->expired_at ? $booking->expired_at->translatedFormat('d F Y H:i') : 'segera';
        $totalAmount = number_format($booking->total_amount, 0, ',', '.');

        $htmlContent = "
            <html>
            <body>
                <h1>Menunggu Pembayaran Tunai</h1>
                <p>Halo <strong>{$booking->leader_name}</strong>,</p>
                <p>Pesanan Anda <strong>#{$booking->order_number}</strong> sedang menunggu pembayaran.</p>
                <p>Silakan lakukan pembayaran tunai sebesar <strong>Rp {$totalAmount}</strong> di loket kasir Taman Nasional Lore Lindu.</p>
                <p>Batas waktu pembayaran: <strong>{$expiredAt}</strong> WITA.</p>
                <p>Jika melewati batas waktu, pesanan akan otomatis dibatalkan.</p>
                <br>
                <p>Terima kasih,</p>
                <p>Tim TNLL Explore</p>
            </body>
            </html>
        ";

        $payload = [
            'sender' => $this->sender,
            'to' => [
                ['email' => $booking->leader_email, 'name' => $booking->leader_name]
            ],
            'subject' => "Instruksi Pembayaran Tunai - #{$booking->order_number}",
            'htmlContent' => $htmlContent
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Send HTTP Request to Brevo
     */
    protected function sendRequest(array $payload)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl, $payload);

            if ($response->successful()) {
                Log::info('Email sent successfully via Brevo: ' . $payload['subject']);
                return true;
            } else {
                Log::error('Brevo Email Failed: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Brevo Connection Error: ' . $e->getMessage());
            return false;
        }
    }
}
