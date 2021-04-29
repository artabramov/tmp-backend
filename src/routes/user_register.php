<?php
$user_email = Flight::request()->query['user_email'];
$user_name  = Flight::request()->query['user_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// register user
$master_user = Flight::user_register( $user_email );

// insert name
$master_name = Flight::attribute_insert( $master_user->id, 'user_name', $user_name, 4, 40 );

// insert hub
$hub = Flight::hub_insert( $master_user->id, 'private', 'my private hub' );

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
