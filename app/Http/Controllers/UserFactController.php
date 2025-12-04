<?php

namespace App\Http\Controllers;

use App\Models\UserFact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserFactController extends Controller
{
    // List all facts for the authenticated user
    public function index()
    {
        $facts = UserFact::where('user_id', Auth::id())->get();

        return Inertia::render('UserFacts', [
            'facts' => $facts
        ]);
    }

    public function list()
    {
        return UserFact::where('user_id', Auth::id())->get();
    }


    // Store a new fact
    public function store(Request $request)
    {
        $data = $request->validate([
            'fact_text' => 'required|string|max:500',
            'importance' => 'required|integer|min:1|max:5',
            'category' => 'nullable|string|max:255'
        ]);

        $fact = UserFact::create([
            'user_id' => Auth::id(),
            'fact_text' => $data['fact_text'],
            'importance' => $data['importance'],
            'category' => $data['category'] ?? null,
        ]);

        return response()->json($fact, 201);
    }

    // Update an existing fact
    public function update(Request $request, UserFact $userFact)
    {
//        $this->authorize('update', $userFact);

        $data = $request->validate([
            'fact_text' => 'sometimes|string|max:500',
            'importance' => 'sometimes|integer|min:1|max:5',
            'category' => 'nullable|string|max:255'
        ]);

        $userFact->update($data);

        return response()->json($userFact);
    }

    // Delete a fact
    public function destroy(UserFact $userFact)
    {
//        $this->authorize('delete', $userFact);

        $userFact->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
