<?php
$user_token = (string) Flight::request()->query['user_token'];
$comment_id = (int) Flight::request()->query['comment_id'];

// self user
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// comment
$comment = new \App\Entities\Comment;
Flight::select( $comment, [
    ['id', '=', $comment_id], 
]);

// post
$post = new \App\Entities\Post;
Flight::select( $post, [
    ['id', '=', $comment->post_id], 
    ['post_status', '<>', 'trash'],
]);

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $post->hub_id], 
    ['hub_status', '<>', 'trash'],
]);

// self role
$role = new \App\Entities\Role;
Flight::select( $role, [
    ['user_id', '=', $user->id], 
    ['hub_id', '=', $hub->id], 
    ['user_role', '=', 'admin']
]);

// delete comment
Flight::delete( $comment );

// json
Flight::json();
