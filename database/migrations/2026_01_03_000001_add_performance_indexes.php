<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Performance indexes for frequently queried columns.
     * Simplified version - only adds indexes for columns that definitely exist.
     */
    public function up(): void
    {
        // Destinations - verified columns from model
        $this->safeAddIndex('destinations', ['is_active', 'is_featured'], 'idx_destinations_active_featured');
        $this->safeAddIndex('destinations', ['slug'], 'idx_destinations_slug');
        $this->safeAddIndex('destinations', ['category_id'], 'idx_destinations_category');

        // Flora - verified columns from model
        $this->safeAddIndex('flora', ['is_active', 'is_featured'], 'idx_flora_active_featured');
        $this->safeAddIndex('flora', ['category'], 'idx_flora_category');
        $this->safeAddIndex('flora', ['sort_order'], 'idx_flora_sort');

        // Fauna - verified columns similar to flora
        $this->safeAddIndex('fauna', ['is_active', 'is_featured'], 'idx_fauna_active_featured');
        $this->safeAddIndex('fauna', ['category'], 'idx_fauna_category');

        // Galleries - verified columns from model
        $this->safeAddIndex('galleries', ['is_active', 'is_featured'], 'idx_galleries_active_featured');
        $this->safeAddIndex('galleries', ['slug'], 'idx_galleries_slug');
        $this->safeAddIndex('galleries', ['gallery_category_id'], 'idx_galleries_category');
        $this->safeAddIndex('galleries', ['sort_order'], 'idx_galleries_sort');

        // Bookings - commonly used columns
        $this->safeAddIndex('bookings', ['order_number'], 'idx_bookings_order');
        $this->safeAddIndex('bookings', ['visit_date'], 'idx_bookings_visit_date');
        $this->safeAddIndex('bookings', ['created_at'], 'idx_bookings_created');

        // Tickets
        $this->safeAddIndex('tickets', ['ticket_code'], 'idx_tickets_code');

        // Wishlists - for user wishlist queries
        $this->safeAddIndex('wishlists', ['user_id', 'destination_id'], 'idx_wishlists_user_dest');

        // Activity logs - for date filtering
        $this->safeAddIndex('activity_logs', ['created_at'], 'idx_activity_logs_created');

        // Visitor logs - for date filtering  
        $this->safeAddIndex('visitor_logs', ['created_at'], 'idx_visitor_logs_created');

        // Comments - for related model lookups
        $this->safeAddIndex('destination_comments', ['destination_id'], 'idx_dest_comments_dest');
        $this->safeAddIndex('flora_comments', ['flora_id'], 'idx_flora_comments_flora');
        $this->safeAddIndex('fauna_comments', ['fauna_id'], 'idx_fauna_comments_fauna');
        $this->safeAddIndex('gallery_comments', ['gallery_id'], 'idx_gallery_comments_gallery');
        $this->safeAddIndex('gallery_likes', ['gallery_id', 'user_id'], 'idx_gallery_likes_gallery_user');
    }

    private function safeAddIndex(string $table, array $columns, string $indexName): void
    {
        try {
            $indexExists = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]))->isNotEmpty();

            if (!$indexExists) {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            }
        } catch (\Exception $e) {
            // Skip if column doesn't exist or other error
            \Log::warning("Could not create index {$indexName}: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        $indexes = [
            'destinations' => ['idx_destinations_active_featured', 'idx_destinations_slug', 'idx_destinations_category'],
            'flora' => ['idx_flora_active_featured', 'idx_flora_category', 'idx_flora_sort'],
            'fauna' => ['idx_fauna_active_featured', 'idx_fauna_category'],
            'galleries' => ['idx_galleries_active_featured', 'idx_galleries_slug', 'idx_galleries_category', 'idx_galleries_sort'],
            'bookings' => ['idx_bookings_order', 'idx_bookings_visit_date', 'idx_bookings_created'],
            'tickets' => ['idx_tickets_code'],
            'wishlists' => ['idx_wishlists_user_dest'],
            'activity_logs' => ['idx_activity_logs_created'],
            'visitor_logs' => ['idx_visitor_logs_created'],
            'destination_comments' => ['idx_dest_comments_dest'],
            'flora_comments' => ['idx_flora_comments_flora'],
            'fauna_comments' => ['idx_fauna_comments_fauna'],
            'gallery_comments' => ['idx_gallery_comments_gallery'],
            'gallery_likes' => ['idx_gallery_likes_gallery_user'],
        ];

        foreach ($indexes as $table => $indexNames) {
            foreach ($indexNames as $indexName) {
                $this->safeDropIndex($table, $indexName);
            }
        }
    }

    private function safeDropIndex(string $table, string $indexName): void
    {
        try {
            $indexExists = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]))->isNotEmpty();

            if ($indexExists) {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            \Log::warning("Could not drop index {$indexName}: " . $e->getMessage());
        }
    }
};
