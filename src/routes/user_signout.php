<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth me
$me = Flight::auth( $user_token );

// update me
Flight::update( $me, [
    'user_token' => Flight::token(),
]);

// json
Flight::json();
