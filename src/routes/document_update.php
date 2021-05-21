<?php
$user_token = (string) Flight::request()->query['user_token'];
$post_id = (int) $post_id;
$post_status = (string) Flight::request()->query['post_status'];
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

// select document
$document = new \App\Entities\Post;
Flight::select( $document, [
    ['id', '=', $post_id], 
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
    ['user_role', 'IN', ['admin', 'author']]
]);

// update document
Flight::update( $document, [
    'post_status' => $post_status,
    'post_excerpt' => $post_excerpt,
]);

// update tags
$document_metas = Flight::sequence( new \App\Entities\Meta, [['parent_type', '=', 'posts'], ['parent_id', '=', $document->id], ['meta_key', '=', 'post_tag']], [] );

foreach( $document_metas as $document_meta ) {
    if( !in_array( $document_meta->meta_value, $post_tags )) {
        Flight::delete( $document_meta );
    }
}

foreach( $post_tags as $post_tag ) {
    $document_meta = new \App\Entities\Meta;
    if( !Flight::exists( $document_meta, [['parent_type', '=', 'posts'], ['parent_id', '=', $document->id], ['meta_key', '=', 'post_tag'], ['meta_value', '=', $post_tag]] )) {
        Flight::insert( $document_meta, [
            'parent_type' => 'posts',
            'parent_id' => $document->id,
            'meta_key' => 'post_tag',
            'meta_value' => $post_tag,
        ]);
    }
}

// json
Flight::json();
