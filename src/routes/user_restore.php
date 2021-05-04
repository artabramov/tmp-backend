<?php
$user_email = Flight::request()->query['user_email'];

// open transaction
Flight::get('pdo')->beginTransaction();

// restore user
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::select( $master, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']
]);

// delay
if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->restore_date ) < 60 ) {
    Flight::set( 'error', 'wait for 60 seconds' );
}

// update master
$user_pass = Flight::pass();
Flight::update( $master, [
    'restore_date' => Flight::time(),
    'user_hash' => Flight::hash( $user_pass ),
]);

// send email
Flight::email( $master->user_email, 'User', 'User restore', 'One-time pass: <i>' . $user_pass . '</i>' );

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
