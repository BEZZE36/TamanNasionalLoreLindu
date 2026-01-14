<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SiteInfoController extends Controller
{
    // Privacy Policy
    public function privacy()
    {
        $content = Setting::getSetting('privacy_policy', '');
        return Inertia::render('Admin/SiteInfo/Privacy', ['content' => $content]);
    }

    public function updatePrivacy(Request $request)
    {
        $request->validate(['content' => 'required|string']);
        Setting::setSetting('privacy_policy', $request->input('content'), 'html');
        return back()->with('success', 'Kebijakan Privasi berhasil diperbarui!');
    }

    // Terms & Conditions
    public function terms()
    {
        $content = Setting::getSetting('terms_conditions', '');
        return Inertia::render('Admin/SiteInfo/Terms', ['content' => $content]);
    }

    public function updateTerms(Request $request)
    {
        $request->validate(['content' => 'required|string']);
        Setting::setSetting('terms_conditions', $request->input('content'), 'html');
        return back()->with('success', 'Syarat & Ketentuan berhasil diperbarui!');
    }

    // About Us
    public function about()
    {
        $content = Setting::getSetting('about_us', '');
        return Inertia::render('Admin/SiteInfo/About', ['content' => $content]);
    }

    public function updateAbout(Request $request)
    {
        $request->validate(['content' => 'required|string']);
        Setting::setSetting('about_us', $request->input('content'), 'html');
        return back()->with('success', 'Tentang Kami berhasil diperbarui!');
    }

    // Contact Info
    public function contact()
    {
        $contact = [
            'address' => Setting::getSetting('contact_address', ''),
            'phone' => Setting::getSetting('contact_phone', ''),
            'whatsapp' => Setting::getSetting('contact_whatsapp', ''),
            'email' => Setting::getSetting('contact_email', ''),
            'maps_embed' => Setting::getSetting('google_maps_embed', ''),
            'operational_hours' => Setting::getSetting('operational_hours', ''),
        ];
        return Inertia::render('Admin/SiteInfo/Contact', ['contact' => $contact]);
    }

    public function updateContact(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'email' => 'nullable|email',
            'maps_embed' => 'nullable|string',
            'operational_hours' => 'nullable|string',
        ]);

        Setting::setSetting('contact_address', $request->address);
        Setting::setSetting('contact_phone', $request->phone);
        Setting::setSetting('contact_whatsapp', $request->whatsapp);
        Setting::setSetting('contact_email', $request->email);
        Setting::setSetting('google_maps_embed', $request->maps_embed);
        Setting::setSetting('operational_hours', $request->operational_hours);

        return back()->with('success', 'Informasi Kontak berhasil diperbarui!');
    }

    // FAQ
    public function faq()
    {
        $faqItems = Setting::getSetting('faq_items', []);
        if (is_string($faqItems)) {
            $faqItems = json_decode($faqItems, true) ?? [];
        }
        return Inertia::render('Admin/FAQ/Index', [
            'faqItems' => $faqItems
        ]);
    }

    public function updateFaq(Request $request)
    {
        $request->validate([
            'questions' => 'array',
            'questions.*' => 'required|string',
            'answers' => 'array',
            'answers.*' => 'required|string',
            'categories' => 'array',
            'categories.*' => 'required|string',
        ]);

        $faqItems = [];
        if ($request->questions) {
            foreach ($request->questions as $index => $question) {
                $faqItems[] = [
                    'question' => $question,
                    'answer' => $request->answers[$index] ?? '',
                    'category' => $request->categories[$index] ?? 'Umum',
                ];
            }
        }

        Setting::setSetting('faq_items', json_encode($faqItems), 'json');
        return back()->with('success', 'FAQ berhasil diperbarui!');
    }

    // Social Media
    public function social()
    {
        $social = [
            'facebook' => Setting::getSetting('facebook_url', ''),
            'instagram' => Setting::getSetting('instagram_url', ''),
            'twitter' => Setting::getSetting('twitter_url', ''),
            'youtube' => Setting::getSetting('youtube_url', ''),
            'tiktok' => Setting::getSetting('tiktok_url', ''),
        ];
        return Inertia::render('Admin/SiteInfo/Social', ['social' => $social]);
    }

    public function updateSocial(Request $request)
    {
        $request->validate([
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'youtube' => 'nullable|url',
            'tiktok' => 'nullable|url',
        ]);

        Setting::setSetting('facebook_url', $request->facebook);
        Setting::setSetting('instagram_url', $request->instagram);
        Setting::setSetting('twitter_url', $request->twitter);
        Setting::setSetting('youtube_url', $request->youtube);
        Setting::setSetting('tiktok_url', $request->tiktok);

        return back()->with('success', 'Sosial Media berhasil diperbarui!');
    }
    // General Settings (Merged from SettingsController)
    public function general()
    {
        $allSettings = Setting::all();
        $groups = Setting::GROUPS;

        // Group settings and transform for Vue
        $settings = [];
        foreach ($groups as $groupKey => $groupLabel) {
            $settings[$groupKey] = $allSettings->where('group', $groupKey)->map(fn($s) => [
                'id' => $s->id,
                'key' => $s->key,
                'label' => $s->label,
                'value' => $s->value,
                'type' => $s->type,
                'description' => $s->description,
            ])->values()->toArray();
        }

        return Inertia::render('Admin/SiteInfo/General', compact('settings', 'groups'));
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                // Handle file uploads
                if ($request->hasFile($key)) {
                    // Delete old file
                    if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                        Storage::disk('public')->delete($setting->value);
                    }
                    $value = $request->file($key)->store('settings', 'public');
                }

                $setting->update(['value' => $value]);
            }
        }

        Setting::clearCache();

        return redirect()->route('admin.site-info.general')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function createSetting()
    {
        $groups = Setting::GROUPS;

        $types = [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'email' => 'Email',
            'url' => 'URL',
            'boolean' => 'Yes/No',
            'image' => 'Image',
            'color' => 'Color',
        ];

        return Inertia::render('Admin/SiteInfo/CreateSetting', compact('groups', 'types'));
    }

    public function storeSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'label' => 'required|string|max:255',
            'value' => 'nullable',
            'type' => 'required|string',
            'group' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        Setting::create($validated);
        Setting::clearCache();

        return redirect()->route('admin.site-info.general')
            ->with('success', 'Pengaturan baru berhasil ditambahkan.');
    }

    public function destroySetting(Setting $setting)
    {
        if ($setting->value && $setting->type === 'image') {
            Storage::disk('public')->delete($setting->value);
        }

        $setting->delete();
        Setting::clearCache();

        return redirect()->route('admin.site-info.general')
            ->with('success', 'Pengaturan berhasil dihapus.');
    }

    public function clearCache()
    {
        Setting::clearCache();
        return back()->with('success', 'Cache pengaturan berhasil dibersihkan.');
    }

    // Detail Web - Combined settings page
    public function detailWeb()
    {
        // Load operational hours - handle both string and array formats
        $operationalHoursData = Setting::getSetting('operational_hours', '{}');
        if (is_array($operationalHoursData)) {
            $operationalHours = $operationalHoursData;
        } else {
            $operationalHours = json_decode($operationalHoursData, true) ?: [];
        }

        // Default operational hours structure
        $defaultHours = [
            'senin' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '16:00'],
            'selasa' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '16:00'],
            'rabu' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '16:00'],
            'kamis' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '16:00'],
            'jumat' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '16:00'],
            'sabtu' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '12:00'],
            'minggu' => ['is_open' => false, 'open_time' => '08:00', 'close_time' => '12:00'],
        ];

        // Merge with defaults
        $operationalHours = array_merge($defaultHours, $operationalHours);

        $data = [
            // Contact Info
            'contact_phone' => Setting::getSetting('contact_phone', ''),
            'contact_whatsapp' => Setting::getSetting('contact_whatsapp', ''),
            'contact_email' => Setting::getSetting('contact_email', ''),
            'contact_latitude' => Setting::getSetting('contact_latitude', '-0.8924096'),
            'contact_longitude' => Setting::getSetting('contact_longitude', '119.8295628'),
            'contact_address' => Setting::getSetting('contact_address', ''),
            'operational_hours' => $operationalHours,
            'google_maps_embed' => Setting::getSetting('google_maps_embed', ''),

            // Social Media
            'facebook_url' => Setting::getSetting('facebook_url', ''),
            'instagram_url' => Setting::getSetting('instagram_url', ''),
            'youtube_url' => Setting::getSetting('youtube_url', ''),
            'tiktok_url' => Setting::getSetting('tiktok_url', ''),
        ];

        return Inertia::render('Admin/SiteInfo/DetailWeb', compact('data'));
    }

    public function updateDetailWeb(Request $request)
    {
        $request->validate([
            'contact_phone' => 'nullable|string|max:50',
            'contact_whatsapp' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_latitude' => 'nullable|string|max:50',
            'contact_longitude' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string',
            'google_maps_embed' => 'nullable|string',
            'facebook_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'youtube_url' => 'nullable|url|max:500',
            'tiktok_url' => 'nullable|url|max:500',
        ]);

        // Save all settings
        Setting::setSetting('contact_phone', $request->contact_phone);
        Setting::setSetting('contact_whatsapp', $request->contact_whatsapp);
        Setting::setSetting('contact_email', $request->contact_email);
        Setting::setSetting('contact_latitude', $request->contact_latitude);
        Setting::setSetting('contact_longitude', $request->contact_longitude);
        Setting::setSetting('contact_address', $request->contact_address);
        Setting::setSetting('google_maps_embed', $request->google_maps_embed);
        Setting::setSetting('facebook_url', $request->facebook_url);
        Setting::setSetting('instagram_url', $request->instagram_url);
        Setting::setSetting('youtube_url', $request->youtube_url);
        Setting::setSetting('tiktok_url', $request->tiktok_url);

        // Save operational hours as JSON
        $operationalHours = [];
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        foreach ($days as $day) {
            $operationalHours[$day] = [
                'is_open' => $request->input("hours_{$day}_open", false) ? true : false,
                'open_time' => $request->input("hours_{$day}_start", '08:00'),
                'close_time' => $request->input("hours_{$day}_end", '16:00'),
            ];
        }
        Setting::setSetting('operational_hours', json_encode($operationalHours), 'json');

        Setting::clearCache();
        \Illuminate\Support\Facades\Cache::forget('site_info_shared');

        return back()->with('success', 'Detail Website berhasil diperbarui!');
    }

    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        try {
            $lat = $request->lat;
            $lon = $request->lon;

            // Use OpenStreetMap Nominatim API
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=18&addressdetails=1";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'TNLL Website/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['display_name'])) {
                    return response()->json([
                        'success' => true,
                        'address' => $data['display_name'],
                        'details' => $data['address'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menemukan alamat untuk koordinat tersebut.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
