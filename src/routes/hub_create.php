<?php
$user_token = Flight::request()->query['user_token'];
$hub_status = Flight::request()->query['hub_status'];
$hub_name = Flight::request()->query['hub_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// user auth
$master_user = Flight::user_auth( $user_token );

// insert hub
$hub = Flight::hub_insert( $master_user->id, $hub_status, $hub_name );

// insert role
$master_role = Flight::role_insert( $hub->id, $master_user->id, 'admin' );

// close transaction
if( empty( Flight::get( 'error' ))) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// debug
if( !empty( Flight::get( 'e' ))) {
    Flight::debug( Flight::get('e') );
}

// json
Flight::json([ 
    'time'    => date( 'Y-m-d H:i:s' ),
    'success' => empty( Flight::get( 'error' )) ? 'true' : 'false',
    'error'   => !empty( Flight::get( 'error' )) ? Flight::get( 'error' ) : '', 
]);
