<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Tag;
use DOMDocument;
use Illuminate\Support\Facades\Http;

class ArticleService
{
    protected array $feeds = [
        // The Conversation feeds (Atom format) - with category mapping
//        'https://theconversation.com/africa/arts/articles.atom' => 'culture-lifestyle',
//        'https://theconversation.com/africa/climate/articles.atom' => 'environment',
//        'https://theconversation.com/africa/business/articles.atom' => 'finance',
//        'https://theconversation.com/africa/education/articles.atom' => 'education',
//        'https://theconversation.com/africa/environment/articles.atom' => 'environment',
//        'https://theconversation.com/africa/health/articles.atom' => 'health',
//        'https://theconversation.com/africa/politics/articles.atom' => 'politics',
//        'https://theconversation.com/africa/technology/articles.atom' => 'technology',
    ];

    protected array $rssFeeds = [
        'https://mwnation.com/category/news/feed/',
//        'https://www.nyasatimes.com/feed/',
//        'https://feeds.bbci.co.uk/news/rss.xml',
    ];

    protected array $conversationCategoryMap = [
        'arts' => 'culture-lifestyle',
        'climate' => 'environment',
        'business' => 'finance',
        'education' => 'education',
        'environment' => 'environment',
        'health' => 'health',
        'politics' => 'politics',
        'technology' => 'technology',
    ];

    public function scrapeFeed(): void
    {
        // Process The Conversation feeds (Atom with categories)
        foreach ($this->feeds as $feed => $category) {
            echo "\n=== Processing Atom feed: {$feed} ===\n";
            $xml = @simplexml_load_file($feed);
            if (!$xml) {
                echo "❌ Failed to load feed: {$feed}\n";
                continue;
            }

            if (isset($xml->entry)) {
                $this->processAtomFeed($xml, $feed, $category);
            } else {
                echo "❌ Not a valid Atom feed: {$feed}\n";
            }
        }

        // Process regular RSS feeds
        foreach ($this->rssFeeds as $feed) {
            echo "\n=== Processing RSS feed: {$feed} ===\n";
            $xml = @simplexml_load_file($feed);
            if (!$xml) {
                echo "❌ Failed to load feed: {$feed}\n";
                continue;
            }

            if (isset($xml->channel)) {
                $this->processRssFeed($xml, $feed, null);
            } else {
                echo "❌ Not a valid RSS feed: {$feed}\n";
            }
        }

        echo "\n=== Feed scraping completed ===\n";
    }

    private function processRssFeed(\SimpleXMLElement $xml, string $feedUrl, ?string $categorySlug): void
    {
        $siteTitle = isset($xml->channel->title) ? (string) $xml->channel->title : '';
        $siteLink = isset($xml->channel->link) ? (string) $xml->channel->link : '';

        $itemCount = 0;
        $skipCount = 0;
        $totalItems = count($xml->channel->item ?? []);
        echo "Found {$totalItems} RSS items\n";

        foreach ($xml->channel->item as $item) {
            $link = (string) $item->link;
            $pubDate = $item->pubDate ? date('Y-m-d', strtotime($item->pubDate)) : null;
            $maxLinkLength = 191;

            if (Article::where('source_url', $link)->exists()) {
                $skipCount++;
                continue;
            }

            $yesterday = date('Y-m-d', strtotime('-1 day'));
            if ($pubDate && $pubDate < $yesterday) {
                echo "⏩ Skipping old article: {$pubDate}\n";
                $skipCount++;
                continue;
            }

            if (strlen($link) > $maxLinkLength) {
                echo "⏩ Skipping - link too long\n";
                $skipCount++;
                continue;
            }

            $title = (string) $item->title;
            $source_url = (string) $item->link;
            $description = $this->cleanHtml($item->description);
            $summary = $this->summarizeArticle($source_url);
            $imageUrl = $this->extractImage($item);

            echo "📝 Creating article: {$title}\n";
            echo "   Summary length: " . strlen($summary ?? '') . " chars\n";

            try {
                $article = Article::create([
                    'title' => $title,
                    'summary' => $summary ?? $description,
                    'date' => $pubDate,
                    'source' => $siteTitle,
                    'source_url' => $source_url,
                    'image_url' => $imageUrl,
                ]);

                echo "🔄 Calling tagArticle API...\n";
                $tags_data = $this->tagArticle($summary ?? $description);

                // Normalize tags
                $flatTags = $this->normalizeTagsForWhereIn($tags_data);
                echo "   Normalized tags: " . (empty($flatTags) ? '[]' : implode(', ', $flatTags)) . "\n";

                $tagIds = [];
                if (empty($flatTags)) {
                    $defaultTag = Tag::where('name', 'General')->first();
                    if ($defaultTag) $tagIds = [$defaultTag->id];
                } else {
                    $tagIds = Tag::whereIn('name', $flatTags)->pluck('id')->toArray();
                    if (empty($tagIds)) {
                        foreach ($flatTags as $tagName) {
                            $cleanName = trim($tagName);
                            if ($cleanName === '') continue;
                            $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s\-]/', '', $cleanName)));
                            $tag = Tag::firstOrCreate(
                                ['name' => $cleanName],
                                ['slug' => $slug, 'description' => 'Auto-generated tag']
                            );
                            $tagIds[] = $tag->id;
                        }
                    }
                }

                if (!empty($tagIds)) $article->tags()->sync($tagIds);

                $itemCount++;
                echo "✅ Added article: {$title}\n";
            } catch (\Exception $e) {
                echo "❌ Error creating article: {$e->getMessage()}\n";
                $skipCount++;
            }

            echo "---\n";
        }

        echo "📊 RSS Results: Added {$itemCount}, Skipped {$skipCount}\n";
    }

    private function processAtomFeed(\SimpleXMLElement $xml, string $feedUrl, ?string $categorySlug): void
    {
        $siteTitle = isset($xml->title) ? (string) $xml->title : 'The Conversation';
        if (!$categorySlug) $categorySlug = $this->extractCategoryFromConversationUrl($feedUrl);

        $tag = Tag::where('slug', $categorySlug)->first();
        if (!$tag) {
            $tagName = ucwords(str_replace('-', ' ', $categorySlug));
            $tag = Tag::firstOrCreate(
                ['slug' => $categorySlug],
                ['name' => $tagName, 'description' => 'Auto-generated from The Conversation']
            );
        }

        $tagIds = [$tag->id];
        echo "✅ Using category: {$categorySlug} (Tag ID: {$tag->id}, Name: {$tag->name})\n";

        $itemCount = 0;
        $skipCount = 0;
        $totalEntries = count($xml->entry ?? []);
        echo "Found {$totalEntries} Atom entries\n";

        foreach ($xml->entry as $entry) {
            $link = $this->extractAtomLink($entry);
            if (!$link) { $skipCount++; continue; }

            $pubDate = isset($entry->published) ? date('Y-m-d', strtotime($entry->published)) : null;
            $maxLinkLength = 191;

            if (Article::where('source_url', $link)->exists()) { $skipCount++; continue; }
            $oneWeekAgo = date('Y-m-d', strtotime('-7 days'));
            if ($pubDate && $pubDate < $oneWeekAgo) { $skipCount++; continue; }
            if (strlen($link) > $maxLinkLength) { $skipCount++; continue; }

            $title = (string) $entry->title;
            $source_url = $link;
            $description = $this->cleanHtml($entry->summary ?? $entry->content ?? '');
            $summary = (string) ($entry->summary ?? $description);
            $imageUrl = $this->extractImageFromAtomContent($entry);

            try {
                $article = Article::create([
                    'title' => $title,
                    'summary' => $summary,
                    'date' => $pubDate,
                    'source' => $siteTitle,
                    'source_url' => $source_url,
                    'image_url' => $imageUrl,
                ]);

                $article->tags()->sync($tagIds);
                $itemCount++;
            } catch (\Exception $e) {
                $skipCount++;
            }
        }

        echo "📊 Atom Results: Added {$itemCount}, Skipped {$skipCount}\n";
    }

    private function extractAtomLink(\SimpleXMLElement $entry): string
    {
        foreach ($entry->link as $link) {
            $attributes = $link->attributes();
            $rel = (string) ($attributes['rel'] ?? '');
            $href = (string) ($attributes['href'] ?? '');
            if ($rel === 'alternate' || $rel === '' || !isset($attributes['rel'])) return $href;
        }
        return isset($entry->link[0]) ? (string) $entry->link[0]->attributes()->href : '';
    }

    private function extractImageFromAtomContent(\SimpleXMLElement $entry): ?string
    {
        if (isset($entry->content)) {
            $content = (string) $entry->content;
            if (preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches)) {
                $imageUrl = $matches[1];
                if (!str_contains($imageUrl, 'counter.theconversation.com')) return $imageUrl;
            }
        }

        $namespaces = $entry->getNamespaces(true);
        if (isset($namespaces['media'])) {
            $media = $entry->children($namespaces['media']);
            foreach (['thumbnail', 'content'] as $key) {
                if (isset($media->$key)) {
                    $attrs = $media->$key->attributes();
                    if (isset($attrs['url'])) return (string) $attrs['url'];
                }
            }
        }

        return null;
    }

    private function extractCategoryFromConversationUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));
        foreach ($parts as $i => $part) {
            if ($part === 'articles.atom' && $i > 0) {
                return $this->conversationCategoryMap[$parts[$i-1]] ?? 'politics';
            }
        }
        return 'politics';
    }

    private function summarizeArticle(string $source_url): ?string
    {
        if (str_contains($source_url, 'theconversation.com')) return null;

        $maxRetries = 1; $attempt = 0;
        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(10)->get('http://127.0.0.1:8000/summarize', ['article_url' => $source_url]);
                if ($response->successful()) return $response->body();
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt > $maxRetries) return 'Failed to get summary';
            }
        }
        return null;
    }

    private function tagArticle($summary)
    {
        if (empty($summary) || str_contains($summary, 'theconversation.com')) return json_encode(['General']);

        $maxRetries = 1; $attempt = 0;
        $default_tags = ['General'];
        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(10)->get('http://127.0.0.1:8000/tag', ['summary' => $summary]);
                if ($response->successful()) return $response->body();
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt > $maxRetries) return json_encode($default_tags);
            }
        }
        return json_encode($default_tags);
    }

    private function normalizeTagsForWhereIn($tagsData): array
    {
        if (is_string($tagsData)) $decoded = json_decode($tagsData, true); else $decoded = $tagsData;
        if (!is_array($decoded)) return [];

        $flat = [];
        foreach ($decoded as $item) {
            if (is_string($item) || is_numeric($item)) { $flat[] = (string)$item; continue; }
            if (is_array($item)) {
                if (isset($item['name'])) { $flat[] = (string)$item['name']; continue; }
                if (isset($item['tag'])) { $flat[] = (string)$item['tag']; continue; }
                foreach ($item as $v) { if (is_string($v) || is_numeric($v)) $flat[] = (string)$v; }
            }
        }
        $flat = array_filter(array_unique(array_map('trim', $flat)), fn($v)=>$v!=='');
        return array_values($flat);
    }

    function cleanHtml($html): string
    {
        if (empty($html)) return '';
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        foreach (iterator_to_array($doc->getElementsByTagName('script')) as $s) $s->parentNode?->removeChild($s);
        foreach (iterator_to_array($doc->getElementsByTagName('style')) as $s) $s->parentNode?->removeChild($s);
        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body) {
            foreach (iterator_to_array($body->childNodes) as $node) {
                $text = $node->textContent;
                if (stripos($text, "The post")!==false||stripos($text,"appeared first on")!==false||stripos($text,"Read more:")!==false){
                    $node->parentNode?->removeChild($node);
                }
            }
            $html = strip_tags($doc->saveHTML($body), '<p><a><strong><em><b><i><ul><ol><li><h1><h2><h3><h4><h5><h6><br>');
            return trim($html);
        }
        return '';
    }

    private function extractImage($item): ?string
    {
        if (!($item instanceof \SimpleXMLElement)) return null;
        if ($item->getName() === 'entry') return $this->extractImageFromAtomContent($item);

        $namespaces = $item->getNameSpaces(true);
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            foreach (['thumbnail','content'] as $key) {
                if (isset($media->$key)) { $attrs = $media->$key->attributes(); if(isset($attrs['url'])) return (string)$attrs['url']; }
            }
        }

        if (isset($item->description)) {
            $doc = new DOMDocument(); libxml_use_internal_errors(true);
            @$doc->loadHTML((string)$item->description); libxml_clear_errors();
            $imgTags = $doc->getElementsByTagName('img');
            if ($imgTags->length>0) return $imgTags->item(0)->getAttribute('src');
        }

        if (isset($namespaces['content'])) {
            $content = $item->children($namespaces['content']);
            if (isset($content->encoded)) {
                $doc = new DOMDocument(); libxml_use_internal_errors(true);
                @$doc->loadHTML((string)$content->encoded); libxml_clear_errors();
                $imgTags = $doc->getElementsByTagName('img');
                if ($imgTags->length>0) return $imgTags->item(0)->getAttribute('src');
            }
        }

        return null;
    }
}
