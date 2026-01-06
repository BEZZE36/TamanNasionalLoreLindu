<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return back()->withErrors([
                'email' => 'Anda harus login terlebih dahulu untuk berlangganan newsletter.'
            ]);
        }

        $user = Auth::user();

        // Check if user already subscribed
        $existingSubscription = NewsletterSubscriber::where('user_id', $user->id)->first();
        if ($existingSubscription) {
            // If disabled by admin, block subscription
            if ($existingSubscription->disabled_by_admin) {
                return back()->withErrors([
                    'email' => 'Langganan newsletter Anda telah dinonaktifkan oleh admin. Hubungi admin untuk mengaktifkan kembali.'
                ]);
            }

            if ($existingSubscription->is_active) {
                return back()->withErrors([
                    'email' => 'Anda sudah berlangganan newsletter.'
                ]);
            } else {
                // Reactivate subscription with new email (user unsubscribed themselves)
                $existingSubscription->update([
                    'email' => strtolower(trim($request->email)),
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'ip_address' => $request->ip(),
                ]);
                return back()->with('newsletter_success', 'Langganan newsletter Anda telah diaktifkan kembali!');
            }
        }

        $validated = $request->validate([
            'email' => 'required|email:rfc,dns|max:255|unique:newsletter_subscribers,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar di newsletter.',
        ]);

        try {
            NewsletterSubscriber::create([
                'user_id' => $user->id,
                'email' => strtolower(trim($validated['email'])),
                'name' => $user->name,
                'is_active' => true,
                'disabled_by_admin' => false,
                'subscribed_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            return back()->with('newsletter_success', 'Terima kasih! Anda telah berlangganan newsletter kami.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Terjadi kesalahan. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request)
    {
        if (!Auth::check()) {
            return back()->withErrors([
                'newsletter' => 'Anda harus login terlebih dahulu.'
            ]);
        }

        $user = Auth::user();
        $subscription = NewsletterSubscriber::where('user_id', $user->id)->first();

        if (!$subscription) {
            return back()->withErrors([
                'newsletter' => 'Anda tidak memiliki langganan newsletter aktif.'
            ]);
        }

        if (!$subscription->is_active) {
            return back()->withErrors([
                'newsletter' => 'Langganan newsletter Anda sudah tidak aktif.'
            ]);
        }

        $subscription->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
            // User unsubscribing themselves does NOT set disabled_by_admin
        ]);

        return back()->with('success', 'Anda telah berhenti berlangganan newsletter.');
    }

    /**
     * Update newsletter email
     */
    public function updateEmail(Request $request)
    {
        if (!Auth::check()) {
            return back()->withErrors([
                'newsletter_email' => 'Anda harus login terlebih dahulu.'
            ]);
        }

        $user = Auth::user();
        $subscription = NewsletterSubscriber::where('user_id', $user->id)->first();

        if (!$subscription) {
            return back()->withErrors([
                'newsletter_email' => 'Anda tidak memiliki langganan newsletter.'
            ]);
        }

        // Block email update if disabled by admin
        if ($subscription->disabled_by_admin) {
            return back()->withErrors([
                'newsletter_email' => 'Langganan newsletter Anda telah dinonaktifkan oleh admin.'
            ]);
        }

        $validated = $request->validate([
            'newsletter_email' => 'required|email:rfc,dns|max:255|unique:newsletter_subscribers,email,' . $subscription->id,
        ], [
            'newsletter_email.required' => 'Email wajib diisi.',
            'newsletter_email.email' => 'Format email tidak valid.',
            'newsletter_email.unique' => 'Email ini sudah digunakan untuk newsletter lain.',
        ]);

        $subscription->update([
            'email' => strtolower(trim($validated['newsletter_email'])),
        ]);

        return back()->with('success', 'Email newsletter berhasil diperbarui!');
    }

    /**
     * Resubscribe to newsletter
     */
    public function resubscribe(Request $request)
    {
        if (!Auth::check()) {
            return back()->withErrors([
                'newsletter' => 'Anda harus login terlebih dahulu.'
            ]);
        }

        $user = Auth::user();
        $subscription = NewsletterSubscriber::where('user_id', $user->id)->first();

        if (!$subscription) {
            return back()->withErrors([
                'newsletter' => 'Anda belum pernah berlangganan newsletter.'
            ]);
        }

        // Block resubscription if disabled by admin
        if ($subscription->disabled_by_admin) {
            return back()->withErrors([
                'newsletter' => 'Langganan newsletter Anda telah dinonaktifkan oleh admin. Hubungi admin untuk mengaktifkan kembali.'
            ]);
        }

        if ($subscription->is_active) {
            return back()->withErrors([
                'newsletter' => 'Anda sudah berlangganan newsletter.'
            ]);
        }

        $subscription->update([
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);

        return back()->with('success', 'Anda telah berlangganan kembali ke newsletter!');
    }
}
