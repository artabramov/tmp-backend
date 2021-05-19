<?php
$user_email = strtolower((string) Flight::request()->query['user_email'] );
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$user = new \App\Entities\User;
Flight::insert( $user, [
    'user_status' => 'pending',
    'user_email' => $user_email,
    'user_name' => $user_name,
    'user_token' => $user->token()
]);

// insert hub
$hub = new \App\Entities\Hub;
Flight::insert( $hub, [
    'user_id' => $user->id,
    'hub_status' => 'private',
    'hub_name' => 'my private repository'
]);

// insert role
$role = new \App\Entities\Role;
Flight::insert( $role, [
    'user_id' => $user->id,
    'hub_id' => $hub->id,
    'user_role' => 'admin'
]);

// insert meta (ip)
$meta = new \App\Entities\Usermeta;
Flight::insert( $meta, [
    'user_id' => $user->id,
    'meta_key' => 'register_ip',
    'meta_value' => Flight::request()->ip
]);

// insert meta (user_agent)
$meta = new \App\Entities\Usermeta;
Flight::insert( $meta, [
    'user_id' => $user->id,
    'meta_key' => 'register_agent',
    'meta_value' => Flight::request()->user_agent
]);

// json
Flight::json();
