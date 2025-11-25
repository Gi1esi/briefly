<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'sender' => 'required|in:user,ai',
            'message' => 'required|string'
        ]);

        $msg = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender' => $request->sender,
            'message' => $request->message,
        ]);

        return response()->json($msg);
    }
    public function messages(Conversation $conversation)
    {
        return $conversation->messages()->orderBy('created_at')->get();
    }
}
