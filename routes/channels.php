<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channel for number broadcasting - no authentication required
Broadcast::channel('numbers', function () {
    return true;
});
