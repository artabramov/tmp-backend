<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];
$user_id = (int) Flight::request()->query['user_id'];

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
]);

// delete his role
Flight::delete( $slave_role );

// json
Flight::json();
