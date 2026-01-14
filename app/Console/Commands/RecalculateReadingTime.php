<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class RecalculateReadingTime extends Command
{
    protected $signature = 'articles:recalculate-reading-time';
    protected $description = 'Recalculate reading time for all articles based on content length';

    public function handle()
    {
        $this->info('Recalculating reading times...');

        $count = 0;
        Article::whereNotNull('content')->chunk(100, function ($articles) use (&$count) {
            foreach ($articles as $article) {
                $oldTime = $article->reading_time;
                $newTime = $article->calculateReadingTime();

                if ($oldTime != $newTime) {
                    $article->update(['reading_time' => $newTime]);
                    $this->line("  Article #{$article->id}: {$oldTime} -> {$newTime} min");
                    $count++;
                }
            }
        });

        $this->info("Done! Updated {$count} articles.");
        return 0;
    }
}
