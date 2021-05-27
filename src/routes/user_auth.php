<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// json
Flight::json([ 'user' => Flight::empty( 'error' ) ? [
    'id'          => $self_user->id, 
    'create_date' => $self_user->create_date, 
    'update_date' => $self_user->update_date, 
    'user_status' => $self_user->user_status, 
    'user_token'  => $self_user->user_token,
    'user_email'  => $self_user->user_email, 
    'user_name'   => $self_user->user_name ] 
    : [],
]);
