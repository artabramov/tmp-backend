<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', 'IN', ['approved', 'premium']]
]);

// select mate
$mate_user = new \App\Entities\User;
Flight::select( $mate_user, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// json
Flight::json([ 'user' => Flight::empty( 'error' ) ? [
    'id'          => $mate_user->id, 
    'create_date' => $mate_user->create_date, 
    'user_name'   => $mate_user->user_name ] 
    : [],
]);
