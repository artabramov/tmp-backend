<?php
$user_token = Flight::request()->query['user_token'];
$hub_id = Flight::request()->query['hub_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// user auth
$master_user = Flight::user_auth( $user_token );

// get hub by hub_id
$hub = Flight::hub_select( $hub_id, 'custom' );

// get master role
$master_role = Flight::role_select( $hub->id, $master_user->id, 'invited' );

// approve the role
$master_role = Flight::role_update( $hub->id, $master_user->id, 'reader' );

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
