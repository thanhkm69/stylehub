<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    if ($user->role === 'admin') {
        return true;
    }
    
    $conversation = \App\Models\Conversation::find($conversationId);
    return $conversation && (int) $conversation->user_id === (int) $user->id;
});
