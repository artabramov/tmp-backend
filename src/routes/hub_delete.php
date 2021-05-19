<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;

// auth
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['user_id', '=', $user->id],
    ['hub_status', '=', 'trash'],
]);

// delete
Flight::delete( $hub );

// json
Flight::json();
