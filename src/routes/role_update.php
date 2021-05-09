<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];
$user_id = (int) Flight::request()->query['user_id'];
$user_role = (string) Flight::request()->query['user_role'];

// me
$me = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $repo_id], 
    ['user_id', '<>', $user_id], 
    ['repo_status', '=', 'custom'],
]);

// my role
$my_role = Flight::role();
Flight::select( $my_role, [
    ['user_id', '=', $me->id], 
    ['repo_id', '=', $repo->id], 
    ['user_role', '=', 'admin']
]);

// he
$he = Flight::user();
Flight::select( $he, [
    ['id', '=', $user_id], 
    ['user_status', '=', 'approved']
]);

// his role
$his_role = Flight::role();
Flight::select( $his_role, [
    ['user_id', '=', $he->id], 
    ['repo_id', '=', $repo->id], 
    ['user_role', '<>', 'none']
]);

// cant set the 'none' role
if( Flight::empty( 'error' ) and $user_role == 'none' ) {
    Flight::set( 'error', 'user_role not available' );
}

// update his role
Flight::update( $his_role, [
    'user_role' => $user_role,
]);

// json
Flight::json();
