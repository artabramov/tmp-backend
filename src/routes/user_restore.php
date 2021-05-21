<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );

// select user
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_email', '=', $user_email], 
    ['user_status', '<>', 'trash']    
]);

// delay over?
$time = Flight::time();
if( Flight::empty( 'error' ) and strtotime( $time ) - strtotime( $self_user->restore_date ) < 60 ) {
    Flight::set( 'error', 'wait for 60 seconds' );
}

// update user
$self_user->user_pass = $self_user->pass();
Flight::update( $self_user, [
    'user_hash' => $self_user->hash( $self_user->user_pass ),
    'restore_date' => Flight::time()
]);

// send email
Flight::email( $self_user->user_email, 'User', 'User restore', 'One-time pass: <i>' . $self_user->user_pass . '</i>' );

// json
Flight::json();
