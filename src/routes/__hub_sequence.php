<?php
$user_token = (string) Flight::request()->query['user_token'];
$offset = (int) Flight::request()->query['offset'];

// self auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']   
]);

// hubs sequence
$sequence = Flight::get('sequence');
$query1 = $sequence->select( ['hub_id'], 'user_roles', [['user_id', '=', $self_user->id], ['user_role', '<>', 'invited']] );
$query2 = $sequence->select( ['*'], 'hubs', [['id', 'IN', $query1]], ['ORDER BY id DESC', 'LIMIT ' . HUBS_SELECT_LIMIT, 'OFFSET ' . $offset]);
$sequence->execute( $query2, new \App\Entities\Hub );


// rows
/*
$query = new \artabramov\Echidna\Query();
$query->text = 'SELECT ' . $select . ' FROM ' . $table . ' WHERE ' . $where . $limits;
$query->args = $this->params( $kwargs );
*/

// roles count
$roles_count = Flight::count( 'user_roles', [['user_id', '=', $self_user->id]] );

// hubs array
$hubs = array_map( fn($val) => [
    'id' => $val->id, 
    'create_date' => $val->create_date,
    'hub_name' => $val->hub_name,
    'roles_count' => $val->roles_count,
    'posts_count' => $val->posts_count,
    ], $sequence->rows );

// json
Flight::json([ 
    'hubs' => $hubs,
    'roles_count' => $roles_count ]);
