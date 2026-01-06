<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Destination;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('usages')
            ->latest()
            ->paginate(15)
            ->through(fn($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'discount_type' => $c->discount_type,
                'discount_value' => $c->discount_value,
                'max_discount' => $c->max_discount,
                'usage_limit' => $c->usage_limit,
                'usages_count' => $c->usages_count,
                'start_date' => $c->start_date,
                'end_date' => $c->end_date,
                'is_active' => $c->is_active,
            ]);

        return Inertia::render('Admin/Coupons/Index', ['coupons' => $coupons]);
    }

    public function create()
    {
        $destinations = Destination::active()->select('id', 'name')->get();
        return Inertia::render('Admin/Coupons/Create', ['destinations' => $destinations]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'applicable_destinations' => 'nullable|array',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon berhasil dibuat.');
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['usages.user', 'usages.booking']);
        return Inertia::render('Admin/Coupons/Show', [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'min_order_amount' => $coupon->min_order_amount,
                'max_discount' => $coupon->max_discount,
                'usage_limit' => $coupon->usage_limit,
                'per_user_limit' => $coupon->per_user_limit,
                'start_date' => $coupon->start_date,
                'end_date' => $coupon->end_date,
                'is_active' => $coupon->is_active,
                'usages' => $coupon->usages->map(fn($u) => [
                    'id' => $u->id,
                    'discount_amount' => $u->discount_amount,
                    'created_at' => $u->created_at,
                    'user' => $u->user ? ['name' => $u->user->name] : null,
                    'booking' => $u->booking ? ['id' => $u->booking->id] : null,
                ])->toArray(),
            ]
        ]);
    }

    public function edit(Coupon $coupon)
    {
        $destinations = Destination::active()->select('id', 'name')->get();
        return Inertia::render('Admin/Coupons/Edit', [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'min_order_amount' => $coupon->min_order_amount,
                'max_discount' => $coupon->max_discount,
                'usage_limit' => $coupon->usage_limit,
                'per_user_limit' => $coupon->per_user_limit,
                'start_date' => $coupon->start_date?->toISOString(),
                'end_date' => $coupon->end_date?->toISOString(),
                'is_active' => $coupon->is_active,
                'applicable_destinations' => $coupon->applicable_destinations,
            ],
            'destinations' => $destinations,
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'applicable_destinations' => 'nullable|array',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon berhasil dihapus.');
    }

    // Toggle active status
    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return back()->with('success', 'Status kupon berhasil diubah.');
    }
}
