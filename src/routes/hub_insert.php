<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_name = (string) Flight::request()->query['hub_name'];

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
]);

// hubs limit reached?
$hub = new \App\Entities\Hub;
if ( Flight::count( 'hubs', [['user_id', '=', $self_user->id]] ) >= HUBS_INSERT_LIMIT ) {
    Flight::set( 'error', 'hubs limit reached' );
}

// hub
Flight::insert( $hub, [
    'user_id' => $self_user->id,
    'hub_name' => $hub_name,
]);

// role
$self_role = new \App\Entities\Role;
Flight::insert( $self_role, [
    'hub_id' => $hub->id,
    'user_id' => $self_user->id,
    'user_role' => 'admin',
]);

// json
Flight::json();
