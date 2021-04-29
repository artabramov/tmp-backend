<?php
$user_token = Flight::request()->query['user_token'];
$hub_id = Flight::request()->query['hub_id'];
$user_id = Flight::request()->query['user_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// user auth
$master_user = Flight::user_auth( $user_token );

// get user by user_id (slave)
$slave_user = Flight::user_select( $user_id, 'approved' );

// get hub by hub_id
$hub = Flight::hub_select( $hub_id, 'custom' );

// get master role
$master_role = Flight::role_select( $hub->id, $master_user->id, 'admin' );

// get slave role
$slave_role = Flight::role_absent( $hub->id, $slave_user->id );

// invite user
$slave_role = Flight::role_insert( $hub->id, $slave_user->id, 'invited' );

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
