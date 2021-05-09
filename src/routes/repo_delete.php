<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) $repo_id;

// me
$me = Flight::auth( $user_token );

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $repo_id], 
    ['user_id', '=', $me->id],
    ['repo_status', '=', 'trash'],
]);

// delete
Flight::delete( $repo );

// json
Flight::json();
