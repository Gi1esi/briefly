<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ScraperService
{
    public static function scrape(string $url)
    {
        try{
            $response = Http::timeout(10)->get($url);

            if(!$response->ok()){
                return null;
            }

            $html = $response->body();

            return self::extractArticleText($html);
        }
        catch (\Exception $e) {
            return null;
        }
    }

    public static function extractArticleText(string $html): string
    {
        $clean_html = strip_tags($html);
        $clean_html = trim(preg_replace('/\s\s+/', ' ', $clean_html));

        return $clean_html;
    }
}
