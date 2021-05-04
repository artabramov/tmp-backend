<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass = (string) Flight::request()->query['user_pass'];

/**
 * No transaction!
 */

// login
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $master, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', Flight::hash( $user_pass ) ], 
    ['user_status', '<>', 'trash']
]);

// update signin date
Flight::update( $master, [
    'signin_date' => Flight::time(), 
]);

// delay, expires and hash check
if( Flight::empty( 'error' ) and $master->user_hash != Flight::hash( $user_pass )) {
    Flight::set( 'error', 'user_pass is wrong' );

} elseif( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->signin_date ) < 1 ) {
    Flight::set( 'error', 'wait for 1 second' );

} elseif( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->restore_date ) > 300 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update auth
Flight::update( $master, [
    'user_status' => 'approved', 
    'user_hash' => '' 
]);

// debug
if( !Flight::empty( 'e' )) {
    Flight::debug( Flight::get('e') );
}

// json
Flight::json([ 
    'time'       => Flight::time(),
    'success'    => Flight::empty( 'error' ) ? 'true' : 'false',
    'error'      => Flight::empty( 'error' ) ? '' : Flight::get( 'error' ), 
    'user_token' => Flight::empty( 'error' ) ? $master->user_token : '',
]);
