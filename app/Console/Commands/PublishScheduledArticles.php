<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';
    protected $description = 'Publish articles that have reached their scheduled_at time';

    public function handle()
    {
        $count = 0;

        // Find articles with scheduled_at in the past
        $articles = Article::where('is_published', true)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($articles as $article) {
            // Set published_at if not already set, then clear scheduled_at
            $updateData = ['scheduled_at' => null];
            if (!$article->published_at) {
                $updateData['published_at'] = $article->scheduled_at;
            }
            $article->update($updateData);
            $count++;
            $this->info("Published: {$article->title}");
        }

        $this->info("Published {$count} scheduled articles.");
        return Command::SUCCESS;
    }
}
