<?php
$user_email = Flight::request()->query['user_email'];
$user_name  = Flight::request()->query['user_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// register user
$master_token = Flight::token();
$master = Flight::user_register( $user_email, $master_token );

// insert name
$master_name = Flight::attribute_insert( $master->id, 'user_name', $user_name, 4, 40 );

// insert hub
$hub = Flight::hub_insert( $master->id, 'private', 'my private hub' );

// insert role
$master_role = Flight::role_insert( $hub->id, $master->id, 'admin' );

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
