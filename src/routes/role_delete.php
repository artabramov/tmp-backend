<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$user_id = (int) Flight::request()->query['user_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::auth( $master, $user_token );

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '=', 'custom'],
]);

// master role
$master_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::select( $master_role, [
    ['user_id', '=', $master->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '=', 'admin']
]);

// slave user
$slave = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $slave, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// additional checks
if( Flight::empty( 'error' ) and $hub->user_id == $slave->id ) {
    Flight::set( 'error', 'user_id not available' );
}

// slave role
$slave_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::select( $slave_role, [
    ['user_id', '=', $slave->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '<>', 'invited']
]);

// delete slave role
Flight::delete( $slave_role );

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
