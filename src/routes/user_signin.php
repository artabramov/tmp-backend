<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass = (string) Flight::request()->query['user_pass'];

// open transaction
Flight::get('pdo')->beginTransaction();

// login
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::load( $master, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', Flight::hash( $user_pass ) ], 
    ['user_status', '<>', 'trash']
]);

// is expires
if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->restore_date ) > 60 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update master
Flight::save( $master, [
    'signin_date' => Flight::time(), 
    'user_status' => 'approved', 
    'user_hash'   => '' 
]);

// close transaction
if( Flight::empty( 'error' )) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

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
