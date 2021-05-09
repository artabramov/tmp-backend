<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_status = (string) Flight::request()->query['repo_status'];
$repo_name = (string) Flight::request()->query['repo_name'];

// me
$me = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::insert( $repo, [
    'user_id' => $me->id,
    'repo_status' => $repo_status,
    'repo_name' => $repo_name,
]);

// role
$role = Flight::role();
Flight::insert( $role, [
    'repo_id' => $repo->id,
    'user_id' => $me->id,
    'user_role' => 'admin',
]);

// json
Flight::json();
