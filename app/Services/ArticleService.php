<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;

class ArticleService
{
    protected array $feeds = [
        'https://feeds.bbci.co.uk/news/rss.xml',
        'https://mwnation.com/category/news/feed/',
        'https://www.nyasatimes.com/feed/',
    ];

    public function scrapeFeed(): void{
        foreach ($this->feeds as $feed) {
            $xml = @simplexml_load_file($feed);
            if (!$xml) continue;

            $siteTitle = isset($xml->channel->title) ? (string) $xml->channel->title : '';
            $siteLink = isset($xml->channel->link) ? (string) $xml->channel->link : '';

            foreach ($xml->channel->item as $item) {
                $link = (string) $item->link;
                $pubDate = $item->pubDate ? date('Y-m-d', strtotime($item->pubDate)) : null;
                $today = date('Y-m-d');
                $maxLinkLength = 191;

                if(Article::where('source_url', $link)->exists() || ($pubDate && $pubDate < $today) || strlen($link) > $maxLinkLength)
                {
                    echo("skipping\n");
                    continue;
                }

                $title = (string) $item->title;
                $source_url  = (string) $item->link;
                $description = cleanHtml($item->description);
//                $content = $this->getFullArticle($source_url);
                $summary_response = $this->summarizeArticle($source_url);
                $summary_data = json_decode($summary_response, true);
                $summary = $summary_data[0];

                $tags = json_decode($summary_data[1], true);

                dd($summary, $tags);

                Article::create([
                    'title' => $title,
                    'summary' => $summary,
                    'date' => $pubDate,
                    'source'=> $siteTitle,
                    'source_url' => $source_url,
                ]);

            }
        }
    }

    private function getFullArticle(string $source_url): string
    {
        //
    }
    private function summarizeArticle(string $source_url): ?string
    {
        $maxRetries = 1;
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(10)->get('http://127.0.0.1:8000/summarize', [
                    'article_url' => $source_url,
                ]);

                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $attempt++;
                if ($attempt > $maxRetries) {
                    logger()->warning("Failed to summarize {$source_url}: {$e->getMessage()}");
                    return 'Failed to get summary';
                }
            }
        }

    }
}
