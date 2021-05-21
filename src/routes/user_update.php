<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_name = (string) Flight::request()->query['user_name'];

// auth
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// update user
if( !empty( $user_name )) {
    Flight::update( $self_user, [
        'user_name' => $user_name
    ]);
}

// json
Flight::json();
