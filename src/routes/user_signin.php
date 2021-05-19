<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );
$user_pass = (string) Flight::request()->query['user_pass'];

// get user
$user = new \App\Entities\User;
Flight::select( $user, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', $user->hash( $user_pass )], 
    ['user_status', '<>', 'trash']   
]);

// pass expired?
$time = Flight::time();
if( Flight::empty( 'error' ) and strtotime( $time ) - strtotime( $user->restore_date ) > 300 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update user
Flight::update( $user, [
    'user_status' => 'approved', 
    'user_hash' => '' 
]);

// json
Flight::json([
    'user_token' => Flight::empty( 'error' ) ? $user->user_token : '',
]);
