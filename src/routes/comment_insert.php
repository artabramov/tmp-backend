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

// upload the files
foreach( Flight::request()->files as $file ) {

    $tmp = explode( '.', $file['name'] );
    $ext = count( $tmp ) > 0 ? '.' . end( $tmp ) : '';
    $upload_mime = mime_content_type( $file['tmp_name'] );
    $upload_name = $file['name'];
    $upload_file = '/echidna/' . $self_user->id . '/' . sha1( date('U') . $file['name'] ) . $ext;

    $upload = new \App\Entities\Upload;
    Flight::insert( $upload, [
        'user_id' => $self_user->id,
        'comment_id' => $comment->id,
        'upload_name' => $upload_name,
        'upload_mime' => $upload_mime,
        'upload_size' => $file['size'],
        'upload_file' => $upload_file,
    ]);

    Flight::upload( $file, $upload->upload_file );
}

// comments sequence
//$tmp = Flight::sequence( new \App\Entities\Comment, [['post_id', '<>', 0]], ['ORDER BY' => 'id DESC'] );

// json
Flight::json();
