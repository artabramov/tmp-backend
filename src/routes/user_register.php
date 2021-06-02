<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$self_user = new \App\Entities\User;
$self_user->user_pass = $self_user->pass();
Flight::insert( $self_user, [
    'restore_date' => Flight::time(),
    'user_status' => 'pending',
    'user_email' => $user_email,
    'user_hash' => $self_user->hash( $self_user->user_pass ),
    'user_name' => $user_name,
    'user_token' => $self_user->token()
]);

// insert hub
$hub = new \App\Entities\Hub;
Flight::insert( $hub, [
    'user_id' => $self_user->id,
    'hub_status' => 'custom',
    'hub_name' => 'my repository'
]);

// insert role
$self_role = new \App\Entities\Role;
Flight::insert( $self_role, [
    'user_id' => $self_user->id,
    'hub_id' => $hub->id,
    'user_role' => 'admin'
]);

// send email
Flight::email( $self_user->user_email, 'User', 'User register', 'One-time pass: <i>' . $self_user->user_pass . '</i>' );

// json
Flight::json();
