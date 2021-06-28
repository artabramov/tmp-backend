<?php
$user_token = (string) Flight::request()->query['user_token'];
$post_id = (int) Flight::request()->query['post_id'];
$comment_text = (string) Flight::request()->query['comment_text'];

// self user
$self_user = new \App\Entities\User;
Flight::auth( $self_user, $user_token );

// document
$document = new \App\Entities\Post;
Flight::select( $document, [
    ['id', '=', $post_id], 
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
    ['user_role', 'IN', ['admin', 'author']]
]);

// insert comment
$comment = new \App\Entities\Comment;
Flight::insert( $comment, [
    'user_id' => $self_user->id,
    'post_id' => $document->id,
    'comment_text' => $comment_text,
]);

// comments sequence
//$tmp = Flight::sequence( new \App\Entities\Comment, [['post_id', '<>', 0]], ['ORDER BY' => 'id DESC'] );

// json
Flight::json();
