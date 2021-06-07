<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
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
