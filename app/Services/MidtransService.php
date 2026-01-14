<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Midtrans\MidtransNotificationHandler;
use App\Services\Midtrans\MidtransPaymentMapper;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Transaction;

class MidtransService
{
    protected MidtransNotificationHandler $notificationHandler;
    protected MidtransPaymentMapper $paymentMapper;

    public function __construct(
        MidtransNotificationHandler $notificationHandler,
        MidtransPaymentMapper $paymentMapper
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->paymentMapper = $paymentMapper;

        $this->configureMiddtrans();
    }

    /**
     * Configure Midtrans SDK
     */
    protected function configureMiddtrans(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        if (app()->environment('local')) {
            $this->configureLocalSsl();
        }
    }

    /**
     * Configure SSL for local development
     */
    protected function configureLocalSsl(): void
    {
        $caPath = 'C:\\laragon\\etc\\ssl\\cacert.pem';
        $options = [10023 => []];

        if (file_exists($caPath)) {
            $options[CURLOPT_CAINFO] = $caPath;
        } else {
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
            $options[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        Config::$curlOptions = $options;
    }

    /**
     * Create Snap Token for payment
     */
    public function createSnapToken(Booking $booking): array
    {
        $params = $this->buildSnapParams($booking);

        try {
            $response = $this->callSnapApi($params, $booking);

            if (!$response['success']) {
                return $response;
            }

            $this->savePaymentRecord($booking, $response);

            return $response;
        } catch (\Exception $e) {
            Log::error('Midtrans Create Snap Token Error: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'order_number' => $booking->order_number,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build Snap API params
     */
    protected function buildSnapParams(Booking $booking): array
    {
        $items = [];

        foreach ($booking->items as $item) {
            $items[] = [
                'id' => 'ITEM-' . $item->id,
                'price' => (int) $item->unit_price,
                'quantity' => $item->quantity,
                'name' => substr($item->label ?? 'Item', 0, 50),
            ];
        }

        if ($booking->service_fee > 0) {
            $items[] = [
                'id' => 'SERVICE-FEE',
                'price' => (int) $booking->service_fee,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }

        // Add discount as negative line item if exists
        if ($booking->discount > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => (int) -$booking->discount,
                'quantity' => 1,
                'name' => 'Diskon Promo' . ($booking->discount_code ? ' (' . $booking->discount_code . ')' : ''),
            ];
        }

        return [
            'transaction_details' => [
                'order_id' => $booking->order_number,
                'gross_amount' => (int) $booking->total_amount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $booking->leader_name ?? 'Customer',
                'email' => $booking->leader_email ?? 'customer@example.com',
                'phone' => $booking->leader_phone ?? '08123456789',
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => (int) config('midtrans.expiry_duration', 1440),
            ],
        ];
    }

    /**
     * Call Snap API to get token
     */
    protected function callSnapApi(array $params, Booking $booking): array
    {
        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production', false);

        $apiUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])
            ->post($apiUrl, $params);

        if ($response->failed()) {
            $errorBody = $response->json();
            Log::error('Midtrans API Error Response', $errorBody ?? ['raw' => $response->body()]);

            return [
                'success' => false,
                'message' => $errorBody['error_messages'][0] ?? $errorBody['status_message'] ?? 'Midtrans API Error',
            ];
        }

        $result = $response->json();

        if (!($result['token'] ?? null)) {
            return ['success' => false, 'message' => 'Tidak ada token dari Midtrans'];
        }

        return [
            'success' => true,
            'snap_token' => $result['token'],
            'redirect_url' => $result['redirect_url'] ?? null,
            'result' => $result,
            'client_key' => config('midtrans.client_key'),
        ];
    }

    /**
     * Save payment record
     */
    protected function savePaymentRecord(Booking $booking, array $response): void
    {
        $payment = Payment::updateOrCreate(
            ['order_id' => $booking->order_number],
            [
                'booking_id' => $booking->id,
                'snap_token' => $response['snap_token'],
                'payment_type' => null,
                'gross_amount' => $booking->total_amount,
                'status' => Payment::STATUS_PENDING,
                'expired_at' => now()->addMinutes(config('midtrans.expiry_duration', 1440)),
                'snap_response' => $response['result'],
            ]
        );

        $response['payment_id'] = $payment->id;
    }

    /**
     * Handle payment notification from Midtrans
     */
    public function handleNotification(): array
    {
        return $this->notificationHandler->handle();
    }

    /**
     * Get payment status from Midtrans
     */
    public function getStatus(string $orderId): ?array
    {
        try {
            $status = Transaction::status($orderId);
            return (array) $status;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                return null;
            }
            Log::error('Midtrans get status error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel payment
     */
    public function cancel(string $orderId): bool
    {
        try {
            Transaction::cancel($orderId);
            return true;
        } catch (\Exception $e) {
            Log::error('Midtrans cancel error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Snap JS URL
     */
    public function getSnapUrl(): string
    {
        return config('midtrans.snap_url');
    }

    /**
     * Get Client Key
     */
    public function getClientKey(): string
    {
        return config('midtrans.client_key');
    }
}
