<?php
$user_token = (string) Flight::request()->query['user_token'];
$offset = (int) Flight::request()->query['offset'];

// self auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']   
]);

// rows
$query = new \artabramov\Echidna\Query();
$query->text = 
"SELECT hubs.*, user_roles.user_role FROM hubs 
LEFT JOIN user_roles ON (hubs.id=user_roles.hub_id AND user_roles.user_id=?)
WHERE hubs.id IN (SELECT user_roles.hub_id FROM user_roles WHERE user_roles.user_id = ?) 
ORDER BY hubs.id DESC 
LIMIT " . HUBS_SELECT_LIMIT . " OFFSET " . $offset;

/*
$query->text = 
"SELECT hubs.id, hubs.create_date, hubs.hub_name, user_roles.user_role, meta.meta_value AS hub_roles_count
FROM hubs 
LEFT JOIN user_roles ON hubs.id=user_roles.hub_id 
LEFT JOIN meta ON (meta.parent_type='hubs' AND meta.parent_id=user_roles.hub_id AND meta_key='hub_roles_count')
WHERE hubs.id IN (SELECT user_roles.hub_id FROM user_roles WHERE user_roles.user_id = ?) 
ORDER BY hubs.id DESC 
LIMIT " . HUBS_SELECT_LIMIT . " 
OFFSET " . $offset;
*/

$query->args = [ $self_user->id, $self_user->id ];
Flight::get('repository')->execute( $query );
$tmp = Flight::get('repository')->rows();

// rows
$rows = array_map( fn($val) => [
    'id' => $val->id, 
    'create_date' => $val->create_date,
    'hub_name' => $val->hub_name,
    'roles_count' => $val->roles_count,
    'posts_count' => $val->posts_count,
    'user_role' => $val->user_role,
    ], $tmp );



// roles count
$roles_count = Flight::count( 'user_roles', [['user_id', '=', $self_user->id]] );

// json
Flight::json([ 
    'rows' => $rows,
    'roles_count' => $roles_count ]);
