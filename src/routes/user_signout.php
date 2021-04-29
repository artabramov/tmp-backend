<?php
$user_token = (string) Flight::request()->query['user_token'];

// ---- open transaction ----
Flight::get('pdo')->beginTransaction();

// ---- auth ----
$me = new \App\Core\User( Flight::get( 'pdo' ));

if( empty( $user_token )) {
    Flight::set( 'error', 'user_token is empty' );

} elseif( !$me->load( [['user_token', '=', $user_token]] )) {
    Flight::set( 'e', $me->e );
    Flight::set( 'error', $me->error );
}

if( !Flight::has_error()) {
    if( empty( $me->id )) {
        Flight::set( 'error', 'user not found' );

    } elseif( $me->user_status != 'approved' ) {
        Flight::set( 'error', 'user_status is not approved' );
    }
}

// ---- signout ----
if( !Flight::has_error()) {
    $me->auth_date = date( 'Y-m-d H:i:s' );
    $me->user_token = $me->create_token();

    if( !$me->save()) {
        Flight::set( 'e', $me->e );
        Flight::set( 'error', $me->error );
    }
}

// ---- close transaction ----
if( !Flight::has_error()) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// ---- write debug ----
if( Flight::has_e()) {
    Flight::debug( Flight::get('e') );
}

// ---- send json ----
Flight::json([ 
    'time'    => Flight::time(),
    'success' => json_encode( !Flight::has_error()),
    'error'   => Flight::has_error() ? Flight::get( 'error' ) : '', 
]);
