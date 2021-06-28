<?php
$user_token = (string) Flight::request()->query['user_token'];
$comment_id = (int) Flight::request()->query['comment_id'];

// is any file here?
$keys = Flight::request()->files->keys();
if( empty( $keys )) {
    Flight::set( 'error', 'file is empty' );
}

// self user
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// comment
$comment = new \App\Entities\Comment;
Flight::select( $comment, [
    ['id', '=', $comment_id], 
    ['user_id', '=', $self_user->id],
]);

// document
$document = new \App\Entities\Post;
Flight::select( $document, [
    ['id', '=', $comment->post_id], 
    ['post_status', '<>', 'trash'],
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
    ['user_role', 'IN', ['admin', 'author', 'editor']]
]);

// non-premium user uploads limit
$self_meta = new \App\Entities\Meta;
if( Flight::empty( 'error' ) and $self_user->user_status != 'premium' and Flight::exists( $self_meta, [['parent_type', '=', 'users'], ['parent_id', '=', $self_user->id], ['meta_key', '=', 'upload_sum']] ) ) {

    Flight::select( $self_meta, [
        ['parent_type', '=', 'users'], 
        ['parent_id', '=', $self_user->id], 
        ['meta_key', '=', 'upload_sum'], 
    ]);

    if( $self_meta->meta_value >= UPLOADS_LIMIT ) {
        Flight::set( 'error', 'uploads limit is over' );
    }
}

// local upload
Flight::upload( $keys, $self_user->id, $comment->id );

// json
Flight::json();
