<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;

// self auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']   
]);

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '<>', 'invited']
]);

// output
$output = [
    'id' => $hub->id, 
    'create_date' => $hub->create_date,
    'hub_name' => $hub->hub_name,
    'roles_count' => $hub->roles_count,
    'posts_count' => $hub->posts_count,
];

// json
Flight::json([ 'hub' => $output ]);
