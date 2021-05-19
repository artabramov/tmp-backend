<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// auth
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// select mate
$mate = new \App\Entities\User;
Flight::select( $mate, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// json
Flight::json([ 'user' => Flight::empty( 'error' ) ? [
    'id'          => $mate->id, 
    'create_date' => $mate->create_date, 
    'user_name'   => $mate->user_name ] 
    : [],
]);
