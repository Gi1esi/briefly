<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with([
            'tags',
            'userRating' => function ($q) {
                $q->where('user_id', auth()->id());
            },
            'userFlag' => fn($q) => $q->where('user_id', auth()->id()),
        ])
            ->orderBy('date', 'desc')
            ->take(3)
            ->get();


        return response()->json($articles);



    }
    /**
     * Show searchable articles.
     */

    public function paginate(request $request)
    {
        $userId = 4;
        $topIds = Article::orderBy('date', 'desc')->take(3)->pluck('id')->toArray();
        $query =  Article::with([
            'tags',
            'userRating' => function ($q) {
                $q->where('user_id', auth()->id());
            },
            'userFlag' => fn($q) => $q->where('user_id', auth()->id()),
        ])
            ->orderBy('date', 'desc')
            ->whereNotIn('id', $topIds);

        \Log::info('Auth debug:', [
            'auth()->id()' => auth()->id(),
            'Auth::id()' => \Auth::id(),
            'auth()->user()' => auth()->user(),
            'Auth::user()' => \Auth::user(),
            'auth()->check()' => auth()->check(),
            'session_id' => session()->getId(),
            'all_session' => session()->all()
        ]);

        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        $articles = $query->paginate(6);
        return response()->json($articles);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function readLater(Request $request)
    {
        $user = $request->user();

        $articles = Article::with(['tags', 'userFlag', 'userRating'])
            ->whereHas('userFlag', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('is_read_later', true);
            })
            ->paginate(12);


        return Inertia::render('Articles/ReadLater', [
            'articles' => $articles->through(fn($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'summary' => $article->summary,
                'image_url' => $article->image_url,
                'source' => $article->source,
                'source_url' => $article->source_url,
                'tags' => $article->tags,
                'liked' => $article->userRating?->rating === 1,
                'disliked' => $article->userRating?->rating === 0,
                'bookmarked' => $article->userFlag?->is_bookmarked ?? false,
                'readLater' => $article->userFlag?->is_read_later ?? false,
                'archived' => $article->userFlag?->is_archived ?? false,
            ])->values()->all(),
            'pagination' => [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
            ]
        ]);
    }

    public function bookmarked()
    {
        $user = auth()->user();

        $articles = Article::whereHas('userFlag', function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->where('is_bookmarked', true);
        })
            ->with(['tags', 'userFlag', 'userRating'])
            ->paginate(6);

        return Inertia::render('Articles/Bookmarked', [
            'articles' => $articles->through(fn($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'summary' => $article->summary,
                'image_url' => $article->image_url,
                'source' => $article->source,
                'source_url' => $article->source_url,
                'tags' => $article->tags,
                'liked' => $article->userRating?->rating === 1,
                'disliked' => $article->userRating?->rating === 0,
                'bookmarked' => $article->userFlag?->is_bookmarked ?? false,
                'readLater' => $article->userFlag?->is_read_later ?? false,
                'archived' => $article->userFlag?->is_archived ?? false,
            ])->values()->all(),
            'pagination' => [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
            ]
        ]);
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
