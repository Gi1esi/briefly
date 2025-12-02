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
            }
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
            'userRating' => function($query) {
                $query->where('user_id')
                    ->whereNull('deleted_at');
            }
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
