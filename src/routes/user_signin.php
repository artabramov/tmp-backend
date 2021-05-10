<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass = (string) Flight::request()->query['user_pass'];

// master
$master = Flight::user();
Flight::select( $master, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', Flight::hash( $user_pass )], 
    ['user_status', '<>', 'trash']   
]);

// pass expired?
$now = Flight::datetime();
if( Flight::empty( 'error' ) and strtotime( $now ) - strtotime( $master->restore_date ) > 300 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update me
Flight::update( $master, [
    'user_status' => 'approved', 
    'user_hash' => '' 
]);

// json
Flight::json([
    'user_token' => Flight::empty( 'error' ) ? $master->user_token : '',
]);
