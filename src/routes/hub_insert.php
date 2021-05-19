<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_status = (string) Flight::request()->query['hub_status'];
$hub_name = (string) Flight::request()->query['hub_name'];

// auth
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::insert( $hub, [
    'user_id' => $user->id,
    'hub_status' => $hub_status,
    'hub_name' => $hub_name,
]);

// role
$role = new \App\Entities\Role;
Flight::insert( $role, [
    'hub_id' => $hub->id,
    'user_id' => $user->id,
    'user_role' => 'admin',
]);

// json
Flight::json();
