<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', 'IN', ['approved', 'premium']]
]);

//Flight::auth( $self_user, $user_token );

// update user
Flight::update( $self_user, [
    'user_token' => $self_user->token()
]);

// json
Flight::json();
