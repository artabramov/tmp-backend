<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_name = (string) Flight::request()->query['user_name'];

// auth
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// update me
if( !empty( $hub_status )) {
    Flight::update( $user, [
        'user_name' => $user_name
    ]);
}

// json
Flight::json();
