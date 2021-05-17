<?php
$user_email = Flight::request()->query['user_email'];
//$user_pass = Flight::pass();

// select user
$user = new \App\Entities\User;
//$user->user_email = $user_email;
Flight::load( $user, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']    
] );

/*
// master
$master = Flight::user();
Flight::select( $master, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']    
]);

// delay over?
$now = Flight::datetime();
if( Flight::empty( 'error' ) and strtotime( $now ) - strtotime( $master->restore_date ) < 60 ) {
    Flight::set( 'error', 'wait for 60 seconds' );
}

// update master
Flight::update( $master, [
    'restore_date' => $now,
    'user_hash' => Flight::hash( $user_pass ),
]);

// send email
Flight::email( $master->user_email, 'User', 'User restore', 'One-time pass: <i>' . $user_pass . '</i>' );
*/

// json
Flight::json();
