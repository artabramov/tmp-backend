<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];

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
    ['user_role', '=', 'none']
]);

// update master role
Flight::update( $my_role, [ 
    'user_role' => 'reader',
]);

// json
Flight::json();
