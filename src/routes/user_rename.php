<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_name = (string) Flight::request()->query['user_name'];

// auth me
$me = Flight::auth( $user_token );

// update me
Flight::update( $me, [
    'user_name' => $user_name
]);

// json
Flight::json();
