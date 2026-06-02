<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    if (strtolower($user->role) === 'admin') {
        return true;
    }
    
    $conversation = \App\Models\Conversation::find($conversationId);
    return $conversation && (int) $conversation->user_id === (int) $user->id;
});

Broadcast::channel('admin.notifications', function ($user) {
    return strtolower($user->role) === 'admin';
});
