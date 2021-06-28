<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$post_excerpt = (string) Flight::request()->query['post_excerpt'];
$post_tags = [];

// post tags
if( !empty( Flight::request()->query['post_tags'] )) {
    $post_tags = explode( ',', mb_strtolower( (string) Flight::request()->query['post_tags'], 'UTF-8' ));
    $post_tags = array_map( fn( $value ) => trim( $value ) , $post_tags );
    $post_tags = array_unique( $post_tags );
}

// self user
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '<>', 'trash'],
]);

// self role
$self_role = new \App\Entities\Role;
Flight::select( $self_role, [
    ['user_id', '=', $self_user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', 'IN', ['admin', 'author']]
]);

// insert document
$document = new \App\Entities\Post;
Flight::insert( $document, [
    'user_id' => $self_user->id,
    'hub_id' => $hub->id,
    'post_type' => 'document',
    'post_status' => 'todo',
    'post_excerpt' => $post_excerpt,
]);

// tags
foreach( $post_tags as $post_tag ) {
    $document_meta = new \App\Entities\Meta;
    Flight::insert( $document_meta, [
        'parent_type' => 'posts',
        'parent_id' => $document->id,
        'meta_key' => 'post_tag',
        'meta_value' => $post_tag,
    ]);
}

// json
Flight::json();
