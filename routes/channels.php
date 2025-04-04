<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('translations.{userId}', function ($user, $userId) {
    return true; // Allow all users for testing. Change later for security.
});