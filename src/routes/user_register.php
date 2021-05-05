<?php
$user_email = Flight::request()->query['user_email'];
$user_name = Flight::request()->query['user_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// user register
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::insert( $master, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'user_status' => 'pending',
    'user_token'  => Flight::token(),
    'user_email'  => $user_email,
    'user_name'   => $user_name,
    'user_hash'   => '',
]);

// hub insert
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::insert( $hub, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $master->id,
    'hub_status'  => 'private',
    'hub_name'    => 'my private hub',
]);

// role insert
$master_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::insert( $master_role, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'hub_id'      => $hub->id,
    'user_id'     => $master->id,
    'user_role'   => 'admin',
]);

/*
// register ip
$master_param = new \App\Core\Param( Flight::get( 'pdo' ));
Flight::insert( $master_param, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $master->id,
    'param_key'   => 'register_ip',
    'param_value' => substr( Flight::request()->ip, 0, 255 ),
]);

// register agent
$master_param = new \App\Core\Param( Flight::get( 'pdo' ));
Flight::insert( $master_param, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $master->id,
    'param_key'   => 'register_agent',
    'param_value' => substr( Flight::request()->user_agent, 0, 255 ),
]);
*/

// restore date
$master_param = new \App\Core\Param( Flight::get( 'pdo' ));
Flight::insert( $master_param, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $master->id,
    'param_key'   => 'restore_date',
    'param_value' => '0001-01-01 00:00:00',
]);

// auth date
$master_param = new \App\Core\Param( Flight::get( 'pdo' ));
Flight::insert( $master_param, [
    'create_date' => Flight::time(),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $master->id,
    'param_key'   => 'auth_date',
    'param_value' => '0001-01-01 00:00:00',
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
