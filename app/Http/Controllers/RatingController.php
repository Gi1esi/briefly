<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function rate(RatingRequest $request)
    {
        $validated = $request->validated();
        $userId = auth()->id();

        $existing = Rating::where('article_id', $validated['article_id'])
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->rating == $validated['rating']) {
                $existing->delete();
                return response()->json(['status' => 'removed']);
            }
            else{
                $existing->update(['rating' => $validated['rating']]);
                return response()->json(['status' => 'updated']);
            }

        }

        Rating::create([
            'article_id' => $validated['article_id'],
            'user_id' => $userId,
            'rating' => $validated['rating'],
        ]);

        return response()->json(['status' => 'created']);
    }
}
