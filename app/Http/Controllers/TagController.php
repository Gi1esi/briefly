<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all('id','name');

        return response()->json($tags);
    }

    public function topics()
    {
        $tags = Tag::withCount('articles')->get();

        return Inertia::render('Articles/Topics', [
            'tags' => $tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'article_count' => $tag->articles_count,
            ]),
        ]);
    }
}
