<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ScraperService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function chat(Request $request, Article $article)
    {
        // Ensure we have article content
        if (!$article->content) {
            $content = ScraperService::scrape($article->source_url);
            if ($content) {
                $article->content = $content;
                $article->save();
            }
        }

        // Find or create conversation for this user + article
        $conversation = Conversation::firstOrCreate([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
        ]);


        $messages = Message::with('conversation')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Sidebar: load all conversations for this user
        $chats = Conversation::with('article')
            ->where('user_id', auth()->id())
            ->get();

        return inertia('Chat/Show', [
            'article' => $article,
            'chats' => $chats,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }


    public function getOrCreate(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
        ]);

        $conversation = Conversation::firstOrCreate([
            'user_id' => auth()->id(),
            'article_id' => $request->article_id,
        ]);

        return response()->json($conversation);
    }
}
