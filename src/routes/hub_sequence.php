<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_status = (string) Flight::request()->query['hub_status'];

// self auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', 'IN', ['approved', 'premium']]   
]);

// hubs sequence
$sequence = Flight::get('sequence');
$query1 = $sequence->select( ['hub_id'], 'user_roles', [['user_id', '=', $self_user->id]] );
$query2 = $sequence->select( ['*'], 'hubs', [['hub_status', '=', $hub_status], ['id', 'IN', $query1]] );
$sequence->execute( $query2, new \App\Entities\Hub );

// hubs array
$hubs = array_map( fn($val) => [
    'id' => $val->id, 
    'create_date' => $val->create_date,
    'hub_status' => $val->hub_status,
    'hub_name' => $val->hub_name,
    'roles_count' => $val->roles_count,
    'posts_count' => $val->posts_count,
    ], $sequence->rows );

// json
Flight::json([ 'hubs' => $hubs ]);
