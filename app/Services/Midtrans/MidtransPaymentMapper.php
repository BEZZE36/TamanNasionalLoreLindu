<?php

namespace App\Services\Midtrans;

class MidtransPaymentMapper
{
    /**
     * Map Midtrans payment type to our enum
     */
    public function mapPaymentType(string $type): string
    {
        return match ($type) {
            'bank_transfer', 'echannel' => 'bank_transfer',
            'gopay' => 'gopay',
            'shopeepay' => 'shopeepay',
            'qris' => 'qris',
            'cstore' => 'cstore',
            'credit_card' => 'credit_card',
            default => 'other',
        };
    }

    /**
     * Determine payment status from Midtrans response
     */
    public function determinePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                return \App\Models\Payment::STATUS_CHALLENGE;
            }
            return \App\Models\Payment::STATUS_SUCCESS;
        }

        return match ($transactionStatus) {
            'settlement' => \App\Models\Payment::STATUS_SUCCESS,
            'pending' => \App\Models\Payment::STATUS_PENDING,
            'deny' => \App\Models\Payment::STATUS_DENY,
            'expire' => \App\Models\Payment::STATUS_EXPIRED,
            'cancel', 'failure' => \App\Models\Payment::STATUS_FAILED,
            'refund' => \App\Models\Payment::STATUS_REFUNDED,
            default => \App\Models\Payment::STATUS_PENDING,
        };
    }
}
