<?php
$user_token = (string) Flight::request()->query['user_token'];

// self auth
$self_user = new \App\Entities\User;
Flight::select( $self_user, [
    ['user_token', '=', $user_token], 
    ['user_status', 'IN', ['approved', 'premium']]   
]);

// hubs list
//$tmp = Flight::query("SELECT hubs.* FROM hubs WHERE id IN (SELECT hub_id FROM roles WHERE user_id=? AND user_role <> 'none')", [$self_user->id]);
$tmp = Flight::query("
    SELECT hubs.*, roles.user_role, meta.meta_value AS role_count
    FROM hubs 
        LEFT JOIN roles ON hubs.id=roles.hub_id 
        LEFT JOIN meta ON meta.parent_id=roles.hub_id
    WHERE hubs.id IN (SELECT hub_id FROM roles WHERE user_id=? AND user_role <> 'none')
        AND meta.parent_type='hubs' AND meta.meta_key='role_count'
    ", [$self_user->id]);

/*
//roles sequence
$tmp_sequence= Flight::sequence( new \App\Entities\Role, [['user_id', '=', $self_user->id], ['user_role', '<>', 'none']], ['ORDER BY' => 'id DESC'] );
$tmp_roles = array_map( fn( $val ) => $val->hub_id, $tmp_sequence );
unset( $tmp_sequence );

// hubs sequence
$hub_sequence = Flight::sequence( new \App\Entities\Hub, [['id', 'IN', $tmp_roles]], ['ORDER BY' => 'id DESC'] );
unset( $tmp_roles );
$hubs = array_map( fn( $hub ) => [ 'create_date' => $hub->create_date, 'hub_name' => $hub->hub_name ], $hub_sequence );
*/

// json
//Flight::json([ 'hubs' => $hubs ]);

Flight::json();
