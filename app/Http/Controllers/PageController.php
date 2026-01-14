<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Feedback;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Display the Contact page
     */
    public function contact()
    {
        $contactInfo = [
            'address' => Setting::getValue('contact_address', 'Sulawesi Tengah, Indonesia'),
            'phone' => Setting::getValue('contact_phone', '+62 812 3456 7890'),
            'email' => Setting::getValue('contact_email', 'info@tnll.id'),
            'whatsapp' => Setting::getValue('contact_whatsapp', ''),
            'hours' => Setting::getValue('contact_hours', 'Senin - Jumat: 08:00 - 17:00'),
        ];
        $mapEmbed = Setting::getValue('contact_map_embed', '');

        return \Inertia\Inertia::render('Public/Contact', compact('contactInfo', 'mapEmbed'));
    }

    /**
     * Handle contact form submission with Brevo email
     */
    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email:rfc,dns|max:100',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+62\s?\d{3}\s?\d{3,4}\s?\d{3,4}$/'],
            'subject' => 'required|string|in:Informasi Umum,Pemesanan Tiket,Kerjasama,Keluhan,Lainnya',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.regex' => 'Format nomor WhatsApp tidak valid.',
            'subject.required' => 'Subjek wajib dipilih.',
            'subject.in' => 'Subjek tidak valid.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal 10 karakter.',
        ]);

        // Save to database
        Feedback::create([
            'user_id' => Auth::id(),
            'display_name' => $validated['name'],
            'contact_email' => $validated['email'],
            'contact_whatsapp' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'unread',
            'ip_address' => $request->ip(),
        ]);

        // Send email via Brevo (async queue for performance)
        try {
            $adminEmail = Setting::getValue('contact_email', config('mail.from.address'));

            Mail::to($adminEmail)->send(new ContactMail([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
            // Don't fail the submission if email fails
        }

        return back()->with('success', 'Pesan Anda berhasil terkirim! Kami akan segera menghubungi Anda.');
    }

    /**
     * Display the FAQ page
     */
    public function faq()
    {
        $faqItems = Setting::getSetting('faq_items', []);
        if (is_string($faqItems)) {
            $faqItems = json_decode($faqItems, true) ?? [];
        }

        // Group FAQ items by category
        $faqCategories = [];
        foreach ($faqItems as $item) {
            $category = $item['category'] ?? 'Umum';
            if (!isset($faqCategories[$category])) {
                $faqCategories[$category] = [];
            }
            $faqCategories[$category][] = $item;
        }

        return \Inertia\Inertia::render('Public/FAQ', compact('faqItems', 'faqCategories'));
    }
}
