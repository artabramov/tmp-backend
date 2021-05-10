<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_name = (string) Flight::request()->query['user_name'];

// auth
$master = Flight::auth( $user_token );

// update me
if( !empty( $user_name )) {
    Flight::update( $master, [
        'user_name' => $user_name
    ]);
}

// json
Flight::json();
