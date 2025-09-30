<?php

namespace App\Services;

use App\Models\Article;

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

                if(Article::where('source_url', $link)->exists()) continue;

                $title = (string) $item->title;
                $source_url  = (string) $item->link;
                $description = cleanHtml($item->description);
                $pubDate = $item->pubDate ? date('Y-m-d', strtotime($item->pubDate)) : null;
                $content = $this->getFullArticle($source_url);
                $summary = $this->summarizeArticle($content);

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
    private function summarizeArticle(string $content): ?string
    {
        //
    }
}
