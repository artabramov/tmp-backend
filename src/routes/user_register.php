<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$self_user = new \App\Entities\User;
Flight::insert( $self_user, [
    'user_status' => 'pending',
    'user_email' => $user_email,
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

/*
// insert meta: ip
$self_meta = new \App\Entities\Meta;
Flight::insert( $self_meta, [
    'parent_type' => 'users',
    'parent_id' => $self_user->id,
    'meta_key' => 'register_ip',
    'meta_value' => Flight::request()->ip
]);

// insert meta: user_agent
$self_meta = new \App\Entities\Meta;
Flight::insert( $self_meta, [
    'parent_type' => 'users',
    'parent_id' => $self_user->id,
    'meta_key' => 'register_agent',
    'meta_value' => Flight::request()->user_agent
]);
*/

// json
Flight::json();
