<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// me
$me = Flight::auth( $user_token );

// select user
$he = Flight::user();
Flight::select( $he, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// json
Flight::json([ 'user' => Flight::empty( 'error' ) ? [
    'id'          => $he->id, 
    'create_date' => $he->create_date, 
    'user_name'   => $he->user_name ] 
    : [],
]);
