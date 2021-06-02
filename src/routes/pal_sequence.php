<?php
$user_token = (string) Flight::request()->query['user_token'];

// auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', 'IN', ['approved', 'premium']]
]);

// users (pals) sequence
$sequence = Flight::get('sequence');
$query1 = $sequence->select( ['pal_id'], 'user_pals', [['user_id', '=', $self_user->id]] );
$query2 = $sequence->select( ['*'], 'users', [['user_status', 'IN', ['approved', 'premium']], ['id', 'IN', $query1]] );
$sequence->execute( $query2, new \App\Entities\User );

// users (pals) array
$users = array_map( fn($val) => [
    'id' => $val->id, 
    'create_date' => $val->create_date,
    'user_status' => $val->user_status,
    'user_name' => $val->user_name
    ], $sequence->rows );

// json
Flight::json([ 'users' => $users ]);
