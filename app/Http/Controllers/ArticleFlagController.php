<?php

namespace App\Http\Controllers;

use App\Models\ArticleFlag;
use Illuminate\Http\Request;

class ArticleFlagController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'flag' => 'required|in:is_bookmarked,is_read_later,is_archived'
        ]);

        $flag = ArticleFlag::firstOrCreate([
            'user_id' => auth()->id(),
            'article_id' => $request->article_id
        ]);

        // Flip the flag
        $flag->{$request->flag} = !($flag->{$request->flag});
        $flag->save();

        return response()->json($flag);
    }
}

