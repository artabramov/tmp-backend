<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$user_id = (int) Flight::request()->query['user_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// do
$master_user = Flight::user_auth( $user_token );
$slave_user = Flight::user_select( $user_id, ['approved'] );

$hub = Flight::hub_select( $hub_id, ['custom'] );

$master_role = Flight::role_select( $hub->id, $master_user->id, ['admin'] );
$slave_role = Flight::role_insert( $hub->id, $slave_user->id, 'invited' );

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
