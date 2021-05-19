<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );

// select user
$user = new \App\Entities\User;
Flight::select( $user, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']    
]);

// delay over?
$time = Flight::time();
if( Flight::empty( 'error' ) and strtotime( $time ) - strtotime( $user->restore_date ) < 60 ) {
    Flight::set( 'error', 'wait for 60 seconds' );
}

// update user
$user->user_pass = $user->pass();
Flight::update( $user, [
    'user_hash' => $user->hash( $user->user_pass ),
    'restore_date' => Flight::time()
]);

// send email
Flight::email( $user->user_email, 'User', 'User restore', 'One-time pass: <i>' . $user->user_pass . '</i>' );

// json
Flight::json();
