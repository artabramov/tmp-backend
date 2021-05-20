<?php
$user_token = (string) Flight::request()->query['user_token'];
$post_id = (int) Flight::request()->query['post_id'];
$post_status = (string) Flight::request()->query['post_status'];
$post_title = (string) Flight::request()->query['post_title'];
$post_tags = [];

// post tags
if( !empty( Flight::request()->query['post_tags'] )) {
    $post_tags = explode( ',', mb_strtolower( (string) Flight::request()->query['post_tags'], 'UTF-8' ));
    $post_tags = array_map( fn( $value ) => trim( $value ) , $post_tags );
    $post_tags = array_unique( $post_tags );
}

// self user
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '<>', 'trash'],
]);

// self role
$role = new \App\Entities\Role;
Flight::select( $role, [
    ['user_id', '=', $user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', 'IN', ['admin', 'author']]
]);

// insert post
$post = new \App\Entities\Post;
Flight::insert( $post, [
    'user_id' => $user->id,
    'hub_id' => $hub->id,
    'post_status' => $post_status,
    'post_title' => $post_title,
]);

// post tags
foreach( $post_tags as $post_tag ) {
    $tag = new \App\Entities\Tag;
    Flight::insert( $tag, [
        'post_id' => $post->id,
        'tag_value' => trim( $post_tag ),
    ]);
}

// json
Flight::json();
