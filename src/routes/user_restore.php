<?php
$user_email = Flight::request()->query['user_email'];

// select the self user
$me = Flight::user();
Flight::select( $me, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']    
]);

// delay
$now = Flight::datetime();
if( Flight::empty( 'error' ) and strtotime( $now ) - strtotime( $me->restore_date ) < 60 ) {
    Flight::set( 'error', 'wait for 60 seconds' );
}

// update master
$me_pass = Flight::pass();
Flight::update( $me, [
    'restore_date' => $now,
    'user_hash' => Flight::hash( $me_pass ),
]);

// send email
Flight::email( $me->user_email, 'User', 'User restore', 'One-time pass: <i>' . $me_pass . '</i>' );

// json
Flight::json();
