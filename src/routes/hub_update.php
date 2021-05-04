<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;
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
Flight::load( $hub, [
    ['id', '=', $hub_id], 
    ['user_id', '=', $doer->id],
    ['hub_status', '<>', 'trash'],
]);

// rename
Flight::save( $hub, [
    'update_date' => Flight::time(),
    'hub_name' => $hub_name,
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
