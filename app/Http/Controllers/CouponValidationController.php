<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponValidationController extends Controller
{
    /**
     * Validate coupon code via API
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'destination_id' => 'nullable|integer',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::findByCode($code);

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode kupon tidak ditemukan.',
            ]);
        }

        $userId = auth()->id();
        $orderAmount = $request->amount ?? 0;
        $destinationId = $request->destination_id;

        $validation = $coupon->isValid($userId, $orderAmount, $destinationId);

        if (!$validation['valid']) {
            return response()->json($validation);
        }

        $discount = $coupon->calculateDiscount($orderAmount);

        return response()->json([
            'valid' => true,
            'message' => 'Kupon berhasil digunakan!',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_label' => $coupon->discount_label,
            ],
            'discount' => $discount,
            'discount_formatted' => 'Rp ' . number_format($discount, 0, ',', '.'),
            'final_amount' => $orderAmount - $discount,
            'final_amount_formatted' => 'Rp ' . number_format($orderAmount - $discount, 0, ',', '.'),
        ]);
    }

    /**
     * Remove applied coupon
     */
    public function remove(Request $request)
    {
        // This is handled client-side, but endpoint for future use
        return response()->json([
            'success' => true,
            'message' => 'Kupon berhasil dihapus.',
        ]);
    }
}
