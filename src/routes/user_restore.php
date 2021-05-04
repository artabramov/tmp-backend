<?php
$user_email = Flight::request()->query['user_email'];

// open transaction
Flight::get('pdo')->beginTransaction();

// restore user
$doer = new \App\Core\User( Flight::get( 'pdo' ));
Flight::load( $doer, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']
]);

// check restore date
if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $doer->restore_date ) < 30 ) {
    Flight::set( 'error', 'wait for 30 seconds' );
}

// update pass and signin date
$doer_pass = Flight::pass();
Flight::save( $doer, [
    'restore_date' => Flight::time(),
    'user_hash'    => Flight::hash( $doer_pass ),
]);

// send email
Flight::email( $doer->user_email, 'User', 'User restore', 'One-time pass: <i>' . $doer_pass . '</i>' );

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
