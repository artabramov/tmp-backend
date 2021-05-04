<?php
$user_email = Flight::request()->query['user_email'];
$user_name  = Flight::request()->query['user_name'];

// open transaction
Flight::get('pdo')->beginTransaction();

// register user
$doer = new \App\Core\User( Flight::get( 'pdo' ));
Flight::save( $doer, [
    'register_date' => Flight::time(),
    'restore_date'  => '0001-01-01 00:00:00',
    'signin_date'   => '0001-01-01 00:00:00',
    'auth_date'     => '0001-01-01 00:00:00',
    'user_status'   => 'pending',
    'user_token'    => Flight::token(),
    'user_email'    => $user_email,
    'user_hash'     => '',
]);

// doer name
$name = new \App\Core\Attribute( Flight::get( 'pdo' ));
Flight::save( $name, [
    'create_date'     => date( 'Y-m-d H:i:s' ),
    'update_date'     => '0001-01-01 00:00:00',
    'user_id'         => $doer->id,
    'attribute_key'   => 'user_name',
    'attribute_value' => $user_name,
]);

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::save( $hub, [
    'create_date' => date( 'Y-m-d H:i:s' ),
    'update_date' => '0001-01-01 00:00:00',
    'user_id'     => $doer->id,
    'hub_status'  => 'private',
    'hub_name'    => 'my private hub',
]);

// doer role
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
