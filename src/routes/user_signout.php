<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth
$master = Flight::auth( $user_token );

// update me
Flight::update( $master, [
    'user_token' => Flight::token(),
]);

// json
Flight::json();
