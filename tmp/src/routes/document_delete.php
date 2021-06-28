<?php
$user_token = (string) Flight::request()->query['user_token'];
$post_id = (int) $post_id;

// self user
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// select document
$document = new \App\Entities\Post;
Flight::select( $document, [
    ['id', '=', $post_id], 
    ['post_status', '=', 'trash'], 
]);

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $document->hub_id], 
    ['hub_status', '<>', 'trash'],
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', 'IN', ['admin']]
]);

// delete document (also document metas delete by the trigger)
Flight::delete( $document );

// json
Flight::json();
