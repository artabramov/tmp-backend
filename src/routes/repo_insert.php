<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_status = (string) Flight::request()->query['repo_status'];
$repo_name = (string) Flight::request()->query['repo_name'];

// auth
$master = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::insert( $repo, [
    'user_id' => $master->id,
    'repo_status' => $repo_status,
    'repo_name' => $repo_name,
]);

// role
$master_role = Flight::role();
Flight::insert( $master_role, [
    'repo_id' => $repo->id,
    'user_id' => $master->id,
    'user_role' => 'admin',
]);

// json
Flight::json();
