<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// master auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::load( $master, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
]);

Flight::save( $master, [ 
    'auth_date' => Flight::time()
]);

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::load( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '=', 'custom'],
]);

// master role
$master_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::load( $master_role, [
    ['user_id', '=', $master->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '=', 'invited']
]);

// update master role
Flight::save( $master_role, [ 
    'update_date' => Flight::time(),
    'user_role' => 'reader',
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
