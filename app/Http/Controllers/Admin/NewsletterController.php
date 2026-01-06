<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    /**
     * Display list of newsletter subscribers
     */
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $subscribers = $query->latest('subscribed_at')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::where('is_active', true)->count(),
            'inactive' => NewsletterSubscriber::where('is_active', false)->count(),
            'this_month' => NewsletterSubscriber::whereMonth('subscribed_at', now()->month)
                ->whereYear('subscribed_at', now()->year)
                ->count(),
        ];

        return Inertia::render('Admin/Newsletter/Index', [
            'subscribers' => $subscribers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Toggle subscriber status
     */
    public function toggleStatus(NewsletterSubscriber $subscriber)
    {
        $wasActive = $subscriber->is_active;

        $subscriber->update([
            'is_active' => !$subscriber->is_active,
            'unsubscribed_at' => $subscriber->is_active ? now() : null,
            // Set disabled_by_admin when admin disables, clear when admin enables
            'disabled_by_admin' => $wasActive ? true : false,
        ]);

        $status = $subscriber->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Subscriber berhasil {$status}.");
    }

    /**
     * Delete subscriber
     */
    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', 'Subscriber berhasil dihapus.');
    }

    /**
     * Bulk delete subscribers
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:newsletter_subscribers,id',
        ]);

        NewsletterSubscriber::whereIn('id', $request->ids)->delete();

        return back()->with('success', count($request->ids) . ' subscriber berhasil dihapus.');
    }

    /**
     * Export subscribers to CSV
     */
    public function export(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->status === 'active') {
            $query->where('is_active', true);
        }

        $subscribers = $query->orderBy('email')->get();

        $filename = 'newsletter_subscribers_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Email', 'Nama', 'Status', 'Tanggal Subscribe', 'IP Address']);

            foreach ($subscribers as $sub) {
                fputcsv($file, [
                    $sub->email,
                    $sub->name ?? '-',
                    $sub->is_active ? 'Aktif' : 'Tidak Aktif',
                    $sub->subscribed_at?->format('Y-m-d H:i:s') ?? '-',
                    $sub->ip_address ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ============ CAMPAIGN MANAGEMENT ============

    /**
     * Display list of newsletter campaigns
     */
    public function campaigns(Request $request)
    {
        $query = NewsletterCampaign::with('creator');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()
            ->paginate(15)
            ->withQueryString();

        // Stats
        $stats = [
            'total' => NewsletterCampaign::count(),
            'draft' => NewsletterCampaign::where('status', 'draft')->count(),
            'sent' => NewsletterCampaign::where('status', 'sent')->count(),
            'activeSubscribers' => NewsletterSubscriber::where('is_active', true)->count(),
        ];

        return Inertia::render('Admin/Newsletter/Campaigns/Index', [
            'campaigns' => $campaigns,
            'stats' => $stats,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show form to create new campaign
     */
    public function createCampaign()
    {
        $activeSubscribers = NewsletterSubscriber::where('is_active', true)->count();

        return Inertia::render('Admin/Newsletter/Campaigns/Create', [
            'activeSubscribers' => $activeSubscribers,
        ]);
    }

    /**
     * Store new campaign
     */
    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ], [
            'subject.required' => 'Subjek email wajib diisi.',
            'content.required' => 'Konten email wajib diisi.',
        ]);

        $campaign = NewsletterCampaign::create([
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'status' => 'draft',
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.newsletter.campaigns.edit', $campaign)
            ->with('success', 'Campaign berhasil dibuat.');
    }

    /**
     * Show form to edit campaign
     */
    public function editCampaign(NewsletterCampaign $campaign)
    {
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.newsletter.campaigns.index')
                ->with('error', 'Campaign tidak dapat diedit.');
        }

        $activeSubscribers = NewsletterSubscriber::where('is_active', true)->count();

        return Inertia::render('Admin/Newsletter/Campaigns/Edit', [
            'campaign' => $campaign,
            'activeSubscribers' => $activeSubscribers,
        ]);
    }

    /**
     * Update campaign
     */
    public function updateCampaign(Request $request, NewsletterCampaign $campaign)
    {
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.newsletter.campaigns.index')
                ->with('error', 'Campaign tidak dapat diedit.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $campaign->update($validated);

        return back()->with('success', 'Campaign berhasil diperbarui.');
    }

    /**
     * Delete campaign
     */
    public function destroyCampaign(NewsletterCampaign $campaign)
    {
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.newsletter.campaigns.index')
                ->with('error', 'Campaign tidak dapat dihapus.');
        }

        $campaign->delete();

        return redirect()->route('admin.newsletter.campaigns.index')
            ->with('success', 'Campaign berhasil dihapus.');
    }

    /**
     * Preview campaign email
     */
    public function previewCampaign(NewsletterCampaign $campaign)
    {
        return view('emails.newsletter', [
            'subject' => $campaign->subject,
            'content' => $campaign->content,
            'recipientName' => 'Preview User',
            'recipientEmail' => 'preview@example.com',
            'unsubscribeUrl' => url('/user/newsletter/unsubscribe'),
        ]);
    }

    /**
     * Send campaign to all active subscribers
     */
    public function sendCampaign(NewsletterCampaign $campaign)
    {
        if (!$campaign->canSend()) {
            return back()->with('error', 'Campaign tidak dapat dikirim.');
        }

        $activeSubscribers = NewsletterSubscriber::where('is_active', true)->count();

        if ($activeSubscribers === 0) {
            return back()->with('error', 'Tidak ada subscriber aktif.');
        }

        // Mark as sending
        $campaign->markAsSending($activeSubscribers);

        // Dispatch job synchronously (no queue worker needed)
        try {
            dispatch_sync(new SendNewsletterJob($campaign));
            return redirect()->route('admin.newsletter.campaigns.index')
                ->with('success', "Campaign berhasil dikirim ke {$activeSubscribers} subscriber.");
        } catch (\Exception $e) {
            $campaign->update(['status' => 'failed']);
            return redirect()->route('admin.newsletter.campaigns.index')
                ->with('error', 'Gagal mengirim: ' . $e->getMessage());
        }
    }

    /**
     * Cancel sending campaign
     */
    public function cancelCampaign(NewsletterCampaign $campaign)
    {
        if ($campaign->status !== 'sending') {
            return back()->with('error', 'Campaign tidak dalam status mengirim.');
        }

        $campaign->update([
            'status' => 'draft',
            'sent_count' => 0,
            'failed_count' => 0,
            'total_recipients' => 0,
        ]);

        return back()->with('success', 'Pengiriman campaign dibatalkan.');
    }

    /**
     * Send test email to admin
     */
    public function sendTestEmail(Request $request, NewsletterCampaign $campaign)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($request->email)
                ->send(new \App\Mail\NewsletterMail($campaign, $request->email, 'Test User'));

            return back()->with('success', 'Email test berhasil dikirim ke ' . $request->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}
