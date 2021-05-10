<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];
$user_id = (int) Flight::request()->query['user_id'];
$user_role = (string) Flight::request()->query['user_role'];

// auth
$master = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $repo_id], 
    ['user_id', '<>', $user_id], 
    ['repo_status', '=', 'custom'],
]);

// my role
$master_role = Flight::role();
Flight::select( $master_role, [
    ['user_id', '=', $master->id], 
    ['repo_id', '=', $repo->id], 
    ['user_role', '=', 'admin']
]);

// he
$slave = Flight::user();
Flight::select( $slave, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// his role
$slave_role = Flight::role();
Flight::select( $slave_role, [
    ['user_id', '=', $slave->id], 
    ['repo_id', '=', $repo->id], 
    ['user_role', '<>', 'none']
]);

// cant set the 'none' role
if( Flight::empty( 'error' ) and $user_role == 'none' ) {
    Flight::set( 'error', 'user_role not available' );
}

// update his role
Flight::update( $slave_role, [
    'user_role' => $user_role,
]);

// json
Flight::json();
