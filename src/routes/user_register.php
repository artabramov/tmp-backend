<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$user = Flight::user([
    'user_status' => 'pending',
    'user_token'  => Flight::token(),
    'user_email'  => $user_email,
    'user_name'   => $user_name,
]);
Flight::save( $user );

// user ip
$usermeta = Flight::usermeta([
    'user_id'    => $user->id,
    'meta_key'   => 'create_ip',
    'meta_value' => mb_substr( Flight::request()->ip, 0, 255, 'UTF-8' ),
]);
Flight::save( $usermeta );

// user agent
$usermeta = Flight::usermeta([
    'user_id'    => $user->id,
    'meta_key'   => 'create_agent',
    'meta_value' => mb_substr( Flight::request()->user_agent, 0, 255, 'UTF-8' ),
]);
Flight::save( $usermeta );

// insert repo
$repo = Flight::repo([
    'user_id'     => $user->id,
    'repo_status' => 'private',
    'repo_name'   => 'my private repo',
]);
Flight::save( $repo );

// insert role
$role = Flight::role([
    'repo_id'   => $repo->id,
    'user_id'   => $user->id,
    'user_role' => 'admin',
]);
Flight::save( $role );

// json
Flight::json();
