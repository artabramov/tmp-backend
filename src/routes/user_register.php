<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$user = new \App\Entities\User;
$user->user_email = $user_email;
$user->user_name = $user_name;
$user->user_pass = $user->pass();
$user->user_hash = $user->hash( $user->user_pass );
$user->user_token = $user->token();
Flight::save( $user );

// insert hub
$hub = new \App\Entities\Hub;
$hub->user_id = $user->id;
$hub->hub_status = 'private';
$hub->hub_name = 'my private repository';
Flight::save( $hub );


/*
// master
$master = Flight::user();
Flight::insert( $master, [
    'user_status' => 'pending',
    'user_token' => Flight::token(),
    'user_email' => $user_email,
    'user_name' => $user_name,
]);

// repo
$repo = Flight::repo();
Flight::insert( $repo, [
    'user_id' => $master->id,
    'repo_status' => 'private',
    'repo_name' => 'my private repository',
]);

// role
$master_role = Flight::role();
Flight::insert( $master_role, [
    'repo_id' => $repo->id,
    'user_id' => $master->id,
    'user_role' => 'admin',
]);
*/

// json
Flight::json();
