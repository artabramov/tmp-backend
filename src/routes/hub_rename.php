<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$hub_name = (string) Flight::request()->query['hub_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// do
$master = Flight::user_auth( $user_token );
$hub = Flight::hub_select( $hub_id, ['private', 'custom'] );

if( Flight::empty( 'error' ) and $hub->user_id != $master->id ) {
    Flight::set( 'error', 'hub_id not available' );
}

Flight::hub_rename( $hub, $hub_name );

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
