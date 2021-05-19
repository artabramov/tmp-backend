<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];

// auth
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '=', 'custom'],
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '=', 'none']
]);

// update master role
Flight::update( $self_role, [ 
    'user_role' => 'reader',
]);

// json
Flight::json();
