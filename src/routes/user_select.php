<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// open transaction
Flight::get('pdo')->beginTransaction();

// auth master
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

// select user
$slave = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $slave, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// update auth
Flight::update( $master, [ 
    'auth_date' => Flight::time()
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
    'user' => Flight::empty( 'error' ) ? [
        'id'            => $slave->id, 
        'register_date' => $slave->register_date, 
        'auth_date'     => $slave->auth_date, 
        'user_status'   => $slave->user_status,
        'user_name'     => $slave->user_name ] : [],
]);
