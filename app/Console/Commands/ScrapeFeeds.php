<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

class ScrapeFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:scrape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape RSS feeds and summarize articles';

    /**
     * Execute the console command.
     */
    public function handle(ArticleService $service): void
    {
        $service->scrapeFeed();
        $this->info('Feeds scraping completed');
    }
}
