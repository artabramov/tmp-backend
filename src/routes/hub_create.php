<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_status = (string) Flight::request()->query['hub_status'];
$hub_name = (string) Flight::request()->query['hub_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth user
$doer = new \App\Core\User( Flight::get( 'pdo' ));
Flight::load( $doer, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
]);

// update auth date
Flight::save( $doer, [ 
    'auth_date' => Flight::time()
]);

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::save( $hub, [
    'create_date' => date( 'Y-m-d H:i:s' ),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $doer->id,
    'hub_status'  => $hub_status,
    'hub_name'    => $hub_name,
]);

// check hub status
if( Flight::empty( 'error' ) and !in_array( $hub_status, ['private', 'custom'] )) {
    Flight::set( 'error', 'hub_status not available' );
}

// role
$role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::save( $role, [
    'create_date' => date( 'Y-m-d H:i:s' ),
    'update_date' => '0001-01-01 00:00:00',
    'hub_id'      => $hub->id,
    'user_id'     => $doer->id,
    'user_role'   => 'admin',
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
