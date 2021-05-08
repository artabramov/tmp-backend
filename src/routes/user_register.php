<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$me = Flight::user();
Flight::insert( $me, [
    'user_status' => 'pending',
    'user_token' => Flight::token(),
    'user_email' => $user_email,
    'user_name' => $user_name,
] );

// json
Flight::json();
