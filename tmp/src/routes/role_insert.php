<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$user_email = (string) Flight::request()->query['user_email'];
$user_role = (string) Flight::request()->query['user_role'];

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
]);

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '=', 'admin']
]);

// mate user
$mate_user = new \App\Entities\User;
Flight::select( $mate_user, [
    ['user_email', '=', $user_email], 
    ['user_status', '=', 'approved']
]);

// mate role exists?
$mate_role = new \App\Entities\Role;
if( Flight::empty( 'error' ) and Flight::exists( $mate_role, [['user_id', '=', $mate_user->id], ['hub_id', '=', $hub->id]] )) {
    Flight::set( 'error', 'user_role already exists' );
}

// insert mate's role
Flight::insert( $mate_role, [
    'hub_id' => $hub->id,
    'user_id' => $mate_user->id,
    'user_role' => $user_role,
]);

// json
Flight::json();
