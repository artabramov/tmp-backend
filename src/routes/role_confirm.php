<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];

// auth
$master = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $repo_id], 
    ['repo_status', '=', 'custom'],
]);

// master role
$master_role = Flight::role();
Flight::select( $master_role, [
    ['user_id', '=', $master->id], 
    ['repo_id', '=', $repo->id], 
    ['user_role', '=', 'none']
]);

// update master role
Flight::update( $master_role, [ 
    'user_role' => 'reader',
]);

// json
Flight::json();
