<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{userOne}.{userTwo}', function ($user, $userOne, $userTwo) {
    return in_array($user->id, [(int) $userOne, (int) $userTwo]);
});
// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
