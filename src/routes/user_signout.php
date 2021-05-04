<?php
$user_token = (string) Flight::request()->query['user_token'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $master, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
]);

// delay and expires
if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->auth_date ) < 1 ) {
    Flight::set( 'error', 'wait for 1 second' );

} elseif( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->restore_date ) > 24 * 60 * 60 ) {
    Flight::set( 'error', 'user_token is expired' );
}

// update auth
Flight::update( $master, [ 
    'auth_date' => Flight::time(),
    'user_token' => Flight::token() 
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
    'time'    => Flight::time(),
    'success' => Flight::empty( 'error' ) ? 'true' : 'false',
    'error'   => Flight::empty( 'error' ) ? '' : Flight::get( 'error' ), 
]);
