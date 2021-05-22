<?php
$user_token = (string) Flight::request()->query['user_token'];
$upload_id = (int) $upload_id;

// self user
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// upload
$upload = new \App\Entities\Upload;
Flight::select( $upload, [
    ['id', '=', $upload_id], 
    ['user_id', '=', $self_user->id],
]);

// comment
$comment = new \App\Entities\Comment;
Flight::select( $comment, [
    ['id', '=', $upload->comment_id], 
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

// remove the upload
if( Flight::empty( 'error' )) {
    if( unlink( $upload->upload_file )) {
        Flight::delete( $upload );
    }
}

// json
Flight::json();
