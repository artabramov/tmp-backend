<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass = (string) Flight::request()->query['user_pass'];

// me
$me = Flight::user();
Flight::select( $me, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', Flight::hash( $user_pass )], 
    ['user_status', '<>', 'trash']   
]);

// pass expired?
$now = Flight::datetime();
if( Flight::empty( 'error' ) and strtotime( $now ) - strtotime( $me->restore_date ) > 300 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update me
Flight::update( $me, [
    'user_status' => 'approved', 
    'user_hash' => '' 
]);

// json
Flight::json([
    'user_token' => Flight::empty( 'error' ) ? $me->user_token : '',
]);
