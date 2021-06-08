<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$offset = (int) Flight::request()->query['offset'];

// self auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']   
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
]);

// rows
$query = new \artabramov\Echidna\Query();
$query->text = 
"SELECT * FROM user_roles 
WHERE hub_id=?
ORDER BY user_roles.id DESC 
LIMIT " . ROLES_SELECT_LIMIT . " OFFSET " . $offset;

// rows
$rows = array_map( fn($val) => [
    'id' => $val->id, 
    'create_date' => $val->create_date,
    'user_id' => $val->user_id,
    'user_role' => $val->user_role,
    ], $tmp );


// json
Flight::json([ 'rows' => $rows ]);
