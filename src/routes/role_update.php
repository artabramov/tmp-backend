<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$user_id = (int) Flight::request()->query['user_id'];
$user_role = (string) Flight::request()->query['user_role'];

// auth
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['user_id', '<>', $user_id], 
    ['hub_status', '=', 'custom'],
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '=', 'admin']
]);

// mate
$mate_user = new \App\Entities\User;
Flight::select( $mate_user, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// mate role
$mate_role = new \App\Entities\Role;
Flight::select( $mate_role, [
    ['user_id', '=', $mate_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '<>', 'none']
]);

// cant set the 'none' role
if( Flight::empty( 'error' ) and $user_role == 'none' ) {
    Flight::set( 'error', 'user_role not available' );
}

// update mate role
Flight::update( $mate_role, [
    'user_role' => $user_role,
]);

// json
Flight::json();
