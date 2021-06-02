<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );
$user_pass = (string) Flight::request()->query['user_pass'];

// signin
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_email', '=', $user_email], 
    ['user_hash', '=', $self_user->hash( $user_pass )], 
    ['user_status', '<>', 'trash']   
]);

// pass expired?
$time = Flight::time();
if( Flight::empty( 'error' ) and strtotime( $time ) - strtotime( $self_user->restore_date ) > 300 ) {
    Flight::set( 'error', 'user_pass is expired' );
}

// update user
Flight::update( $self_user, [
    'user_status' => 'approved', 
    'user_hash' => '' 
]);

// json
Flight::json([ 'user' => Flight::empty( 'error' ) ? [
    'id'            => $self_user->id, 
    'create_date'   => $self_user->create_date, 
    'update_date'   => $self_user->update_date, 
    'user_status'   => $self_user->user_status, 
    'user_token'    => $self_user->user_token,
    'user_email'    => $self_user->user_email, 
    'user_name'     => $self_user->user_name,
    'uploads_count' => $self_user->uploads_count, 
    'uploads_sum'   => $self_user->uploads_sum ] 
    : [],
]);
