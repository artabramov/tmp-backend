<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// do
$master = Flight::user_auth( $user_token );
$hub = Flight::hub_select( $hub_id, ['custom'] );
$master_role = Flight::role_select( $hub->id, $master->id, ['invited'] );
$master_role = Flight::role_update( $master_role, 'reader' );

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
