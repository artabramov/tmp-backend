<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) $repo_id;
$repo_status = (string) Flight::request()->query['repo_status'];
$repo_name = (string) Flight::request()->query['repo_name'];

// auth
$master = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $repo_id], 
    ['user_id', '=', $master->id],
]);

// update status
if( !empty( $repo_status )) {
    Flight::update( $repo, [
        'repo_status' => $repo_status,
    ]);
}

// update name
if( !empty( $repo_name )) {
    Flight::update( $repo, [
        'repo_name' => $repo_name,
    ]);
}

// json
Flight::json();
