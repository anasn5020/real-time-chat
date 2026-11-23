<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id1}.{id2}', function ($user, $id1, $id2) {
    $id1 = (int) $id1;
    $id2 = (int) $id2;
    return in_array($user->id, [$id1, $id2]);
});
// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
