<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );
$user_pass = (string) Flight::request()->query['user_pass'];

// get user
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', $self_user->hash( $user_pass )], 
    ['user_status', '<>', 'trash']   
]);

// pass expired?
$time = Flight::time();
if( Flight::empty( 'error' ) and strtotime( $time ) - strtotime( $self_user->restore_date ) > 300 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update user
Flight::update( $self_user, [
    'user_status' => 'approved', 
    'user_hash' => '' 
]);

// json
Flight::json([
    'user_token' => Flight::empty( 'error' ) ? $self_user->user_token : '',
]);
