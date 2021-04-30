<?php
$user_email = Flight::request()->query['user_email'];

// open transaction
Flight::get('pdo')->beginTransaction();

// restore user
$master_pass = Flight::pass();
$master = Flight::user_restore( $user_email, $master_pass );

// send email
Flight::email( $master->user_email, 'User', 'User restore', 'One-time pass: <i>' . $master_pass . '</i>' );

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
