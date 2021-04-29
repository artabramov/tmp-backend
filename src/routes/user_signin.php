<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass  = (string) Flight::request()->query['user_pass'];

// open transaction
Flight::get('pdo')->beginTransaction();

// primary data check
if( empty( $user_email )) {
    Flight::set( 'error', 'user_email is empty' );

} elseif( empty( $user_pass )) {
    Flight::set( 'error', 'user_pass is empty' );
}

// signin user
$me = new \App\Core\User( Flight::get( 'pdo' ));
if( !$me->load( [['user_email', '=', $user_email], ['user_hash', '=', $me->get_hash($user_pass)]] )) {
    Flight::set( 'e', $me->e );
    Flight::set( 'error', $me->error );
}

// check user data
if( !Flight::has_error()) {
    if( empty( $me->id )) {
        Flight::set( 'error', 'user not found' );

    } elseif( $me->user_status == 'trash' ) {
        Flight::set( 'error', 'user_status is trashed' );

    } elseif( date( 'U' ) - date( 'U', strtotime( $me->restore_date )) > 300 ) {
        Flight::set( 'error', 'user_pass is expired' );
    }
}

// update user data
if( !Flight::has_error()) {
    $me->user_status = 'approved';
    $me->signin_date = date( 'Y-m-d H:i:s' );
    $me->user_hash = '';

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
    'user_token' => !Flight::has_error() ? $me->user_token : '',
]);

