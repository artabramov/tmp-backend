<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];
$user_id = (int) Flight::request()->query['user_id'];

// me
$me = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $repo_id], 
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

// his role exists?
$exist = Flight::exist();
if( Flight::empty( 'error' ) and $exist->has( 'user_roles', [['user_id', '=', $user_id], ['repo_id', '=', $repo_id]] )) {
    Flight::set( 'error', 'user_role already exists' );
}

// insert his role
$his_role = Flight::role();
Flight::insert( $his_role, [
    'repo_id' => $repo->id,
    'user_id' => $he->id,
    'user_role' => 'none',
]);

// json
Flight::json();
