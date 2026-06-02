<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\NewSupportMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get all conversations for admin
     */
    public function getConversations()
    {
        $conversations = Conversation::with(['user:id,name,email', 'messages' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(1);
        }])
        ->withCount(['messages as unread_count' => function ($q) {
            $q->where('is_read', false)
              ->whereHas('sender', function ($sq) {
                  $sq->whereRaw('LOWER(role) != ?', ['admin']);
              });
        }])
        ->orderBy('last_message_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get or create conversation for the current customer
     */
    public function myConversation()
    {
        $user = Auth::user();
        
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id],
            ['last_message_at' => now()]
        );

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages(Conversation $conversation)
    {
        $user = Auth::user();

        // Security check: Only admin or the conversation owner can read
        if (strtolower($user->role) !== 'admin' && $conversation->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $messages = $conversation->messages()->with('sender:id,name')->get();

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $user = Auth::user();

        // Security check
        if (strtolower($user->role) !== 'admin' && $conversation->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Load sender relation before broadcasting
        $message->load('sender:id,name');

        broadcast(new MessageSent($message))->toOthers();

        // Notify admin channel when a customer sends a message
        if (strtolower($user->role) !== 'admin') {
            broadcast(new NewSupportMessage($message));
        }

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Get unread message count for admin
     */
    public function unreadCount()
    {
        $count = Message::where('is_read', false)
            ->whereHas('sender', function ($q) {
                $q->whereRaw('LOWER(role) != ?', ['admin']);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }
}
