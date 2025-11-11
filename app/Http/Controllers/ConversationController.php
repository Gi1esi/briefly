<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Conversation;
use App\Services\ScraperService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function chat(Request $request, Article $article)
    {
        if(!$article->content){
            $content = ScraperService::scrape($article->source_url);

            if($content){
                $article->content = $content;
                $article->save();
            }
        }

        $chats = Conversation::where('user_id', auth()->id())->get();

        return inertia::render('Chat/Show',[
            'article' => $article,
            'chats' => $chats,
        ]);
    }
}
