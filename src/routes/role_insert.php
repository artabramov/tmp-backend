<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$user_id = (int) Flight::request()->query['user_id'];

// self user
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
    ['user_role', '=', 'admin']
]);

// mate
$mate_user = new \App\Entities\User;
Flight::select( $mate_user, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// his role exists?
$mate_role = new \App\Entities\Role;
if( Flight::empty( 'error' ) and Flight::exists( $mate_role, [['user_id', '=', $user_id], ['hub_id', '=', $hub_id]] )) {
    Flight::set( 'error', 'user_role already exists' );
}

// insert his role
Flight::insert( $mate_role, [
    'hub_id' => $hub->id,
    'user_id' => $mate_user->id,
    'user_role' => 'none',
]);

// json
Flight::json();
