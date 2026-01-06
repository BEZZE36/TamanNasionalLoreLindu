<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        // Use manifest hash for cache busting when available
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            return md5_file($manifestPath);
        }

        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Check both guards - web (regular users) and admin using Auth facade
        $user = Auth::guard('web')->user() ?? Auth::guard('admin')->user();

        // Get newsletter subscription for current user
        $newsletterSubscription = null;
        if ($user && !($user instanceof \App\Models\Admin)) {
            $newsletterSubscription = NewsletterSubscriber::where('user_id', $user->id)->first();
        }

        return [
            ...parent::share($request),
            'tinymce_api_key' => config('tinymce.api_key'),
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                    'status' => $user->status ?? 'active',
                    'is_blocked' => ($user->status ?? 'active') === 'blocked',
                    'avatar' => $user->avatar_url ?? ($user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=059669&color=fff'),
                    'member_since' => $user->created_at?->format('d M Y'),
                    // Use lazy loading for wishlist to avoid querying on every request
                    'wishlisted_destination_ids' => Inertia::lazy(
                        fn() =>
                        method_exists($user, 'wishlist')
                        ? Cache::remember(
                            "user_{$user->id}_wishlist",
                            300,
                            fn() =>
                            $user->wishlist()->pluck('destination_id')->toArray()
                        )
                        : []
                    ),
                    'is_admin' => $user instanceof \App\Models\Admin,
                ] : null,
                // Newsletter subscription for current user
                'newsletterSubscription' => $newsletterSubscription ? [
                    'email' => $newsletterSubscription->email,
                    'is_active' => $newsletterSubscription->is_active,
                ] : null,
            ],
            // Admin data for admin panel (includes role and permissions for access checks)
            'admin' => function () use ($request) {
                $admin = Auth::guard('admin')->user();
                if (!$admin)
                    return null;

                // Load permissions from roles
                $admin->load('roles.permissions');
                $permissions = $admin->roles->flatMap(function ($role) {
                    return $role->permissions->pluck('slug');
                })->unique()->values()->toArray();

                // Get permission level from middleware (if set)
                $permissionLevel = $request->attributes->get('permissionLevel', 'write');
                $menuGroup = $request->attributes->get('menuGroup', null);

                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'username' => $admin->username,
                    'role' => $admin->role,
                    'is_active' => $admin->is_active,
                    'avatar' => $admin->avatar ? asset('storage/' . $admin->avatar) : null,
                    'permissions' => $permissions,
                    'permissionLevel' => $permissionLevel, // 'write', 'read', 'access', 'none'
                    'menuGroup' => $menuGroup, // Current menu group being accessed
                ];
            },
            // Shared site info for footer and other components (cached for 10 minutes)
            'siteInfo' => Cache::remember('site_info_shared', 600, function () {
                // Get operational hours and parse if needed
                $operationalHoursData = Setting::getSetting('operational_hours', '{}');
                if (is_string($operationalHoursData)) {
                    $operationalHours = json_decode($operationalHoursData, true) ?: [];
                } else {
                    $operationalHours = $operationalHoursData ?: [];
                }

                return [
                    'contact_phone' => Setting::getSetting('contact_phone', ''),
                    'contact_whatsapp' => Setting::getSetting('contact_whatsapp', ''),
                    'contact_email' => Setting::getSetting('contact_email', 'info@tnll.id'),
                    'contact_address' => Setting::getSetting('contact_address', 'Sulawesi Tengah, Indonesia'),
                    'contact_latitude' => Setting::getSetting('contact_latitude', '-0.8924096'),
                    'contact_longitude' => Setting::getSetting('contact_longitude', '119.8295628'),
                    'google_maps_embed' => Setting::getSetting('google_maps_embed', ''),
                    'facebook_url' => Setting::getSetting('facebook_url', ''),
                    'instagram_url' => Setting::getSetting('instagram_url', ''),
                    'youtube_url' => Setting::getSetting('youtube_url', ''),
                    'tiktok_url' => Setting::getSetting('tiktok_url', ''),
                    'operational_hours' => $operationalHours,
                ];
            }),
        ];
    }
}
