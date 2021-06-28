<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;

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
    ['user_id', '=', $self_user->id],
]);

// delete
Flight::delete( $hub );

// json
Flight::json();