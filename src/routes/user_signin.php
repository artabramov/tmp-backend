<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass = (string) Flight::request()->query['user_pass'];

// open transaction
Flight::get('pdo')->beginTransaction();

// is pass empty
if( Flight::empty( 'error' ) and empty( $user_pass )) {
    Flight::set( 'error', 'user_pass is empty' );
}

// login
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $master, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', Flight::hash( $user_pass ) ], 
    ['user_status', '<>', 'trash']
]);

// is pass expired
if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->restore_date ) > 120 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update user
Flight::update( $master, [
    'update_date' => Flight::time(), 
    'user_status' => 'approved', 
    'user_hash' => '' 
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
