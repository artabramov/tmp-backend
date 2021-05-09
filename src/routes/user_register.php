<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_name = (string) Flight::request()->query['user_name'];

// me
$me = Flight::user();
Flight::insert( $me, [
    'user_status' => 'pending',
    'user_token' => Flight::token(),
    'user_email' => $user_email,
    'user_name' => $user_name,
]);

// repo
$repo = Flight::repo();
Flight::insert( $repo, [
    'user_id' => $me->id,
    'repo_status' => 'private',
    'repo_name' => 'my private repository',
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
