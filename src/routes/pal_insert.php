<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) Flight::request()->query['user_id'];

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', 'IN', ['approved', 'premium']]
]);

// mate user
$mate_user = new \App\Entities\User;
Flight::select( $mate_user, [
    ['id', '=', $user_id], 
    ['id', '<>', $self_user->id], 
    ['user_status', 'IN', ['approved', 'premium']]
]);

// pal exists?
$pal = new \App\Entities\Pal;
if( Flight::empty( 'error' ) and Flight::exists( $pal, [['user_id', '=', $self_user->id], ['pal_id', '=', $user_id]] )) {
    Flight::set( 'error', 'pal already exists' );
}

// insert pal
Flight::insert( $pal, [
    'user_id' => $self_user->id,
    'pal_id' => $user_id
]);

// json
Flight::json();
