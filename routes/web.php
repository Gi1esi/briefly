<?php

use Illuminate\Support\Facades\Route;
use  willvincent\Feeds\Facades\FeedsFacade;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/feeds', function () {
    $feeds = [
        'https://feeds.bbci.co.uk/news/rss.xml',
        'https://mwnation.com/category/news/feed/',
        'https://www.nyasatimes.com/feed/',

    ];
    $items = [];
    foreach ($feeds as $feed) {
        $xml = simplexml_load_file($feed);

        $siteTitle = isset($xml->channel->title) ? (string) $xml->channel->title : '';
        $siteLink = isset($xml->channel->link) ? (string) $xml->channel->link : '';

        foreach ($xml->channel->item as $item) {
            $description = cleanHtml($item->description);
            $items[] = [
                'siteTitle' => $siteTitle,
                'siteLink' => $siteLink,
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'description' => $description,
                'pubDate' => (string) $item->pubDate,
            ];
        }
    }
    dd($items);
});



