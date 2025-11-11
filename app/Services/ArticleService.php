<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Tag;
use DOMDocument;
use Illuminate\Support\Facades\Http;

class ArticleService
{
    protected array $feeds = [
        'https://mwnation.com/category/news/feed/',
        'https://feeds.bbci.co.uk/news/rss.xml',
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
                $description = $this->cleanHtml($item->description);
//                $content = $this->getFullArticle($source_url);
                $summary = $this->summarizeArticle($source_url);
                $imageUrl = $this->extractImage($item);


                $article = Article::create([
                    'title' => $title,
                    'summary' => $summary,
                    'date' => $pubDate,
                    'source'=> $siteTitle,
                    'source_url' => $source_url,
                    'image_url' => $imageUrl,
                ]);

                $tags_data = $this->tagArticle($summary);
                $tags = json_decode($tags_data, true);
                $tagIds = Tag::whereIn('name', $tags)->pluck('id')->toArray();
                $article->tags()->sync($tagIds);



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

    private function tagArticle($summary)
    {
        $maxRetries = 1;
        $attempt = 0;
        $default_tags = ['Politics'];

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(10)->get('http://127.0.0.1:8000/tag', [
                    'summary' => $summary,
                ]);

                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $attempt++;
                if ($attempt > $maxRetries) {
                    logger()->warning("Failed to tag summary: {$e->getMessage()}");

                    return $default_tags;
                }
            }
        }
    }


    function cleanHtml($html): false|string
    {
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        $body = $doc->getElementsByTagName('body')->item(0);

        foreach (iterator_to_array($body->childNodes) as $node) {
            $text = $node->textContent;
            if (stripos($text, "The post") !== false || stripos($text, "appeared first on") !== false) {
                $body->removeChild($node);
            }
        }

        return $doc->saveHTML($body);
    }

    private function extractImage($item): ?string
    {
        $namespaces = $item->getNameSpaces(true);

        // 1. Check for media:thumbnail or media:content (BBC style)
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if (isset($media->thumbnail)) {
                $attrs = $media->thumbnail->attributes();
                if (isset($attrs['url'])) {
                    return (string) $attrs['url'];
                }
            }
            if (isset($media->content)) {
                $attrs = $media->content->attributes();
                if (isset($attrs['url'])) {
                    return (string) $attrs['url'];
                }
            }
        }

        // 2. Look inside description for an <img> tag (Nation & Nyasa)
        if (isset($item->description)) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML((string) $item->description);
            libxml_clear_errors();

            $imgTags = $doc->getElementsByTagName('img');
            if ($imgTags->length > 0) {
                return $imgTags->item(0)->getAttribute('src');
            }
        }

        // 3. Check content:encoded for an <img> tag (Nyasa often uses this)
        if (isset($namespaces['content'])) {
            $content = $item->children($namespaces['content']);
            if (isset($content->encoded)) {
                $doc = new DOMDocument();
                libxml_use_internal_errors(true);
                $doc->loadHTML((string) $content->encoded);
                libxml_clear_errors();

                $imgTags = $doc->getElementsByTagName('img');
                if ($imgTags->length > 0) {
                    return $imgTags->item(0)->getAttribute('src');
                }
            }
        }

        return null;
    }


}
