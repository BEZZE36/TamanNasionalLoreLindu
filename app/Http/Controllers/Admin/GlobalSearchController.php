<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Admin;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json(['results' => [], 'menus' => []]);
        }

        $results = [];
        $limit = 5;

        // ======================
        // CONTENT SEARCH (with try-catch per model)
        // ======================

        // 1. Search Destinations
        try {
            $destinations = Destination::where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orWhere('city', 'like', "%{$query}%")
                ->limit($limit)
                ->get();

            foreach ($destinations as $dest) {
                $results[] = [
                    'title' => $dest->name,
                    'subtitle' => $dest->city ?? 'Destinasi Wisata',
                    'url' => route('admin.destinations.edit', $dest->id),
                    'icon' => 'map-pin',
                    'group' => 'Destinasi',
                    'type' => 'content'
                ];
            }
        } catch (\Exception $e) {
            // Skip this model if error
        }

        // 2. Search Flora
        try {
            if (class_exists('App\Models\Flora')) {
                $floras = \App\Models\Flora::where('name', 'like', "%{$query}%")
                    ->orWhere('scientific_name', 'like', "%{$query}%")
                    ->limit($limit)
                    ->get();

                foreach ($floras as $flora) {
                    $results[] = [
                        'title' => $flora->name,
                        'subtitle' => $flora->scientific_name ?? 'Flora',
                        'url' => route('admin.flora.edit', $flora->id),
                        'icon' => 'leaf',
                        'group' => 'Flora',
                        'type' => 'content'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 3. Search Fauna
        try {
            if (class_exists('App\Models\Fauna')) {
                $faunas = \App\Models\Fauna::where('name', 'like', "%{$query}%")
                    ->orWhere('scientific_name', 'like', "%{$query}%")
                    ->limit($limit)
                    ->get();

                foreach ($faunas as $fauna) {
                    $results[] = [
                        'title' => $fauna->name,
                        'subtitle' => $fauna->scientific_name ?? 'Fauna',
                        'url' => route('admin.fauna.edit', $fauna->id),
                        'icon' => 'bird',
                        'group' => 'Fauna',
                        'type' => 'content'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 4. Search Articles
        try {
            if (class_exists('App\Models\Article')) {
                $articles = \App\Models\Article::where('title', 'like', "%{$query}%")
                    ->limit($limit)
                    ->get();

                foreach ($articles as $article) {
                    $results[] = [
                        'title' => $article->title,
                        'subtitle' => 'Artikel',
                        'url' => route('admin.articles.edit', $article->id),
                        'icon' => 'file-text',
                        'group' => 'Artikel',
                        'type' => 'content'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 5. Search News
        try {
            if (class_exists('App\Models\News')) {
                $news = \App\Models\News::where('title', 'like', "%{$query}%")
                    ->limit($limit)
                    ->get();

                foreach ($news as $item) {
                    $results[] = [
                        'title' => $item->title,
                        'subtitle' => 'Berita',
                        'url' => route('admin.news.edit', $item->id),
                        'icon' => 'newspaper',
                        'group' => 'Berita',
                        'type' => 'content'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 6. Search Announcements (column: message, not content)
        try {
            if (class_exists('App\Models\Announcement')) {
                $announcements = \App\Models\Announcement::where('title', 'like', "%{$query}%")
                    ->orWhere('message', 'like', "%{$query}%")
                    ->limit($limit)
                    ->get();

                foreach ($announcements as $ann) {
                    $results[] = [
                        'title' => $ann->title,
                        'subtitle' => 'Pengumuman',
                        'url' => route('admin.announcements.edit', $ann->id),
                        'icon' => 'megaphone',
                        'group' => 'Pengumuman',
                        'type' => 'content'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 7. Search Users
        try {
            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit($limit)
                ->get();

            foreach ($users as $user) {
                $results[] = [
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'url' => route('admin.users.edit', $user->id),
                    'icon' => 'users',
                    'group' => 'Pengguna',
                    'type' => 'content'
                ];
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 8. Search Bookings
        try {
            $bookings = Booking::where('order_number', 'like', "%{$query}%")
                ->limit($limit)
                ->get();

            foreach ($bookings as $booking) {
                $results[] = [
                    'title' => 'Order #' . $booking->order_number,
                    'subtitle' => $booking->user ? $booking->user->name : 'Guest',
                    'url' => route('admin.bookings.show', $booking->id),
                    'icon' => 'clipboard-list',
                    'group' => 'Pemesanan',
                    'type' => 'content'
                ];
            }
        } catch (\Exception $e) {
            // Skip
        }

        // 9. Search Admins
        try {
            $admins = Admin::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit($limit)
                ->get();

            foreach ($admins as $admin) {
                $results[] = [
                    'title' => $admin->name,
                    'subtitle' => $admin->email,
                    'url' => route('admin.admins.edit', $admin->id),
                    'icon' => 'user-circle',
                    'group' => 'Admin',
                    'type' => 'content'
                ];
            }
        } catch (\Exception $e) {
            // Skip
        }

        // ======================
        // MENU SEARCH
        // ======================
        $menus = $this->searchMenus($query);

        return response()->json([
            'results' => $results,
            'menus' => $menus
        ]);
    }

    private function searchMenus($query)
    {
        $allMenus = [
            ['name' => 'Dashboard', 'url' => '/admin/dashboard', 'icon' => 'layout-dashboard', 'keywords' => 'dashboard home beranda'],
            ['name' => 'Destinasi', 'url' => '/admin/destinations', 'icon' => 'map-pin', 'keywords' => 'destinasi tempat wisata danau lindu'],
            ['name' => 'Tambah Destinasi', 'url' => '/admin/destinations/create', 'icon' => 'map-pin', 'keywords' => 'tambah destinasi baru'],
            ['name' => 'Flora', 'url' => '/admin/flora', 'icon' => 'leaf', 'keywords' => 'flora tumbuhan tanaman'],
            ['name' => 'Tambah Flora', 'url' => '/admin/flora/create', 'icon' => 'leaf', 'keywords' => 'tambah flora baru'],
            ['name' => 'Fauna', 'url' => '/admin/fauna', 'icon' => 'bird', 'keywords' => 'fauna hewan binatang'],
            ['name' => 'Tambah Fauna', 'url' => '/admin/fauna/create', 'icon' => 'bird', 'keywords' => 'tambah fauna baru'],
            ['name' => 'Gallery', 'url' => '/admin/gallery', 'icon' => 'image', 'keywords' => 'gallery galeri foto gambar'],
            ['name' => 'Upload Foto', 'url' => '/admin/gallery/create', 'icon' => 'image', 'keywords' => 'upload foto gambar baru'],
            ['name' => 'Pengumuman', 'url' => '/admin/announcements', 'icon' => 'megaphone', 'keywords' => 'pengumuman announcement'],
            ['name' => 'Artikel', 'url' => '/admin/articles', 'icon' => 'file-text', 'keywords' => 'artikel tulisan'],
            ['name' => 'Berita', 'url' => '/admin/news', 'icon' => 'newspaper', 'keywords' => 'berita news'],
            ['name' => 'Newsletter', 'url' => '/admin/newsletter', 'icon' => 'mail', 'keywords' => 'newsletter email subscriber'],
            ['name' => 'Edit Info', 'url' => '/admin/site-info/general', 'icon' => 'settings', 'keywords' => 'edit info situs website'],
            ['name' => 'Pemesanan', 'url' => '/admin/bookings', 'icon' => 'clipboard-list', 'keywords' => 'pemesanan booking order tiket'],
            ['name' => 'Kupon', 'url' => '/admin/coupons', 'icon' => 'ticket', 'keywords' => 'kupon coupon diskon promo'],
            ['name' => 'Pengguna', 'url' => '/admin/users', 'icon' => 'users', 'keywords' => 'pengguna user member'],
            ['name' => 'Scan Tiket', 'url' => '/admin/tickets', 'icon' => 'qr-code', 'keywords' => 'scan tiket qr'],
            ['name' => 'Activity Log', 'url' => '/admin/activity-logs', 'icon' => 'clock', 'keywords' => 'activity log riwayat aktivitas'],
            ['name' => 'Analytics', 'url' => '/admin/analytics', 'icon' => 'bar-chart-3', 'keywords' => 'analytics statistik grafik'],
            ['name' => 'Kelola Admin', 'url' => '/admin/admins', 'icon' => 'user-circle', 'keywords' => 'kelola admin administrator'],
            ['name' => 'Role & Akses', 'url' => '/admin/roles', 'icon' => 'shield', 'keywords' => 'role akses permission hak'],
            ['name' => 'Database', 'url' => '/admin/database', 'icon' => 'database', 'keywords' => 'database backup restore'],
        ];

        $query = strtolower($query);

        return array_values(array_filter($allMenus, function ($menu) use ($query) {
            return str_contains(strtolower($menu['name']), $query) ||
                str_contains(strtolower($menu['keywords']), $query);
        }));
    }
}
