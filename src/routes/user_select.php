<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// auth
$master = Flight::auth( $user_token );

// select user
$slave = Flight::user();
Flight::select( $slave, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// json
Flight::json([ 'user' => Flight::empty( 'error' ) ? [
    'id'          => $slave->id, 
    'create_date' => $slave->create_date, 
    'user_name'   => $slave->user_name ] 
    : [],
]);
