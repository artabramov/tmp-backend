<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::auth( $master, $user_token );

// select user
$slave = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $slave, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

$slave_auth_date = new \App\Core\Param( Flight::get( 'pdo' ));
Flight::select( $slave_auth_date, [
    ['user_id', '=', $slave->id], 
    ['param_key', '=', 'auth_date']
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
    'user' => Flight::empty( 'error' ) ? [
        'id'          => $slave->id, 
        'create_date' => $slave->create_date, 
        'auth_date'   => $slave_auth_date->param_value, 
        'user_name'   => $slave->user_name ] : [],
]);
