<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;
$offset = (int) Flight::request()->query['offset'];

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

// hub data
$hub_data = [
    'id' => $hub->id, 
    'create_date' => $hub->create_date,
    'hub_name' => $hub->hub_name,
    'roles_count' => $hub->roles_count,
    'posts_count' => $hub->posts_count,
];

// roles data
$query = new \artabramov\Echidna\Query();
$query->text = 
"SELECT * FROM user_roles 
WHERE hub_id=?
ORDER BY user_roles.id DESC 
LIMIT " . ROLES_SELECT_LIMIT . " OFFSET " . $offset;

$query->args = [ $hub->id ];
Flight::get('repository')->execute( $query );
$tmp = Flight::get('repository')->rows();

$roles_data = array_map( fn($val) => [
    'id' => $val->id, 
    'create_date' => $val->create_date,
    'user_id' => $val->user_id,
    'user_role' => $val->user_role,
    ], $tmp );

// roles count
$roles_count = Flight::count( 'user_roles', [['hub_id', '=', $hub->id]] );

// json
Flight::json([ 
    'hub' => $hub_data,
    'roles' => $roles_data,
    'roles_count' => $roles_count
     ]);
