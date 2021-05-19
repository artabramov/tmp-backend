<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// update user
Flight::update( $user, [
    'user_token' => $user->token()
]);

// json
Flight::json();
