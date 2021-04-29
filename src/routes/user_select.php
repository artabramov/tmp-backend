<?php
$user_token = (string) Flight::request()->query['user_token'];
$user_id = (int) $user_id;

// open transaction
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

// user select
$user = new \App\Core\User( Flight::get( 'pdo' ));

if( empty( $user_id )) {
    Flight::set( 'error', 'user_id is empty' );

} elseif( !$user->load( [['id', '=', $user_id]] )) {
    Flight::set( 'e', $user->e );
    Flight::set( 'error', $user->error );
}

if( !Flight::has_error()) {
    if( empty( $user->id )) {
        Flight::set( 'error', 'user not found' );
    }
}

// ---- update auth date ----
if( !Flight::has_error()) {
    $me->auth_date = date( 'Y-m-d H:i:s' );

    if( !$me->save()) {
        Flight::set( 'e', $me->e );
        Flight::set( 'error', $me->error );
    }
}

// close transaction
if( !Flight::has_error()) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// write debug
if( Flight::has_e()) {
    Flight::debug( Flight::get('e') );
}

// send json
Flight::json([ 
    'time'    => Flight::time(),
    'success' => json_encode( !Flight::has_error()),
    'error'   => Flight::has_error() ? Flight::get( 'error' ) : '', 
    'user'    => Flight::has_error() ? [] : [ 
        'id'            => $user->id, 
        'register_date' => $user->register_date, 
        'auth_date'     => $user->auth_date, 
        'user_status'   => $user->user_status ],
]);
