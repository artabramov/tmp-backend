<?php
$user_email = (string) Flight::request()->query['user_email'];

// open transaction
Flight::get('pdo')->beginTransaction();

// restore user
$master = Flight::user_restore( $user_email );

// update pass
$master = $master->create_pass();
Flight::user_repass( $master, $master_pass );

/*
// create pass
if( !Flight::has_error()) {
    $user_pass = $me->create_pass();
    $me->user_hash = $me->get_hash( $user_pass );

    if( !$me->save()) {
        Flight::set( 'e', $me->e );
        Flight::set( 'error', $me->error );
    }
}

// can send email?
if( !Flight::has_error()) {
    if( date( 'U' ) - date( 'U', strtotime( $me->restore_date )) < 30 ) {
        Flight::set( 'error', 'wait for 30 seconds' );
    }
}

// send email
if( !Flight::has_error()) {
    Flight::email( $me->user_email, 'User', 'User restore', 'One-time pass: <i>' . $user_pass . '</i>' );
}

// update restore date
if( !Flight::has_error()) {
    $me->restore_date = date( 'Y-m-d H:i:s' );

    if( !$me->save()) {
        Flight::set( 'e', $me->e );
        Flight::set( 'error', $me->error );
    }
}
*/

// close transaction
if( empty( Flight::get( 'error' ))) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// debug
if( !empty( Flight::get( 'e' ))) {
    Flight::debug( Flight::get('e') );
}

// json
Flight::json([ 
    'time'    => date( 'Y-m-d H:i:s' ),
    'success' => empty( Flight::get( 'error' )) ? 'true' : 'false',
    'error'   => !empty( Flight::get( 'error' )) ? Flight::get( 'error' ) : '', 
]);
