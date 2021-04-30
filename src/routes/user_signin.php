<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_pass  = (string) Flight::request()->query['user_pass'];

// open transaction
Flight::get('pdo')->beginTransaction();

// signin
$master = Flight::user_signin( $user_email, $user_pass );

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
    'time'       => Flight::time(),
    'success'    => Flight::empty( 'error' ) ? 'true' : 'false',
    'error'      => Flight::empty( 'error' ) ? '' : Flight::get( 'error' ), 
    'user_token' => Flight::empty( 'error' ) ? $master->user_token : '',
]);
