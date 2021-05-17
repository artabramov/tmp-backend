<?php
$user_email = (string) Flight::request()->query['user_email'];
$user_name = (string) Flight::request()->query['user_name'];

// insert user
$user = new \App\Entities\User;
$user->user_email = $user_email;
$user->user_name = $user_name;
//$user->user_pass = $user->pass();
//$user->user_hash = $user->hash( $user->user_pass );
$user->user_token = $user->token();
Flight::save( $user );

// insert hub
$hub = new \App\Entities\Hub;
$hub->user_id = $user->id;
$hub->hub_status = 'private';
$hub->hub_name = 'my private repository';
Flight::save( $hub );

// insert role
$role = new \App\Entities\Role;
$role->user_id = $user->id;
$role->hub_id = $hub->id;
$role->user_role = 'admin';
Flight::save( $role );

// insert meta (ip)
$meta = new \App\Entities\Usermeta;
$meta->user_id = $user->id;
$meta->meta_key = 'register_ip';
$meta->meta_value = Flight::request()->ip;
Flight::save( $meta );

// insert meta (user_agent)
$meta = new \App\Entities\Usermeta;
$meta->user_id = $user->id;
$meta->meta_key = 'register_agent';
$meta->meta_value = Flight::request()->user_agent;
Flight::save( $meta );

// json
Flight::json();
