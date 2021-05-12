<?php
$user_token = (string) Flight::request()->query['user_token'];
$parent_id = (int) Flight::request()->query['parent_id'];
$repo_id = (int) Flight::request()->query['repo_id'];
$post_status = (string) Flight::request()->query['post_status'];
$post_content = (string) Flight::request()->query['post_content'];
$post_tags = (string) Flight::request()->query['post_tags'];

// auth
$master = Flight::auth( $user_token );

// parent document
if( empty( $parent_id )) {

    // repo
    $repo = Flight::repo();
    Flight::select( $repo, [
        ['id', '=', $repo_id], 
        ['repo_status', '<>', 'trash'],
    ]);

    // master role
    $master_role = Flight::role();
    Flight::select( $master_role, [
        ['user_id', '=', $master->id], 
        ['repo_id', '=', $repo->id], 
    ]);

    // additional checks
    if( Flight::empty( 'error' ) and !in_array( $master_role->user_role, ['admin', 'author'] )) {
        Flight::set( 'error', 'user_role must be admin or author' );

    } elseif( Flight::empty( 'error' ) and !in_array( $post_status, ['todo', 'doing', 'done']) ) {
        Flight::set( 'error', 'post_status must be todo, doing or done' );
    }

    // post insert
    $post = Flight::post();
    Flight::insert( $post, [
        'user_id'      => $master->id,
        'repo_id'      => $repo->id,
        'post_status'  => $post_status,
        'post_content' => $post_content,
    ]);

    // post tags
    if( !empty( $post_tags )) {
        $tmp = explode( ',', $post_tags );
        foreach( $tmp as $meta_value ) {
            $meta = Flight::postmeta();
            Flight::insert( $meta, [
                'post_id'      => $post->id,
                'meta_key'     => 'post_tag',
                'meta_value'   => trim( mb_strtolower( $meta_value, 'UTF-8' )),
            ]);
        }
    }

// child comment
} else {

    // parent
    $parent = Flight::post();
    Flight::select( $parent, [
        ['id', '=', $parent_id], 
        ['post_status', '<>', 'trash'],
    ]);

    // repo
    $repo = Flight::repo();
    Flight::select( $repo, [
        ['id', '=', $parent->repo_id], 
        ['repo_status', '<>', 'trash'],
    ]);

    // master role
    $master_role = Flight::role();
    Flight::select( $master_role, [
        ['user_id', '=', $master->id], 
        ['repo_id', '=', $repo->id], 
    ]);

    // additional checks
    if( Flight::empty( 'error' ) and !in_array( $master_role->user_role, ['admin', 'author', 'editor'] )) {
        Flight::set( 'error', 'user_role must be admin, author or editor' );

    } elseif( Flight::empty( 'error' ) and !in_array( $parent->post_status, ['todo', 'doing', 'done']) ) {
        Flight::set( 'error', 'parent post_status must be todo, doing or done' );
    }

    // post insert
    $post = Flight::post();
    Flight::insert( $post, [
        'parent_id' => $parent->id,
        'user_id' => $master->id,
        'repo_id' => $repo->id,
        'post_status' => 'comment',
        'post_content' => $post_content,
    ]);

}

// upload the files
foreach( Flight::request()->files as $file ) {

    $tmp = explode( '.', $file['name'] );
    $ext = count( $tmp ) > 0 ? '.' . end( $tmp ) : '';
    $upload_mime = mime_content_type( $file['tmp_name'] );
    $upload_name = $file['name'];
    $upload_file = '/echidna/' . $master->id . '/' . sha1( date('U') . $file['name'] ) . $ext;

    $upload = Flight::upload();
    Flight::insert( $upload, [
        'user_id'     => $master->id,
        'repo_id'     => $repo->id,
        'post_id'     => $post->id,
        'upload_key'  => 'none',
        'upload_name' => mb_substr( $upload_name, 0, 255, 'UTF-8' ),
        'upload_mime' => mb_substr( $upload_mime, 0, 255, 'UTF-8' ),
        'upload_size' => $file['size'],
        'upload_file' => mb_substr( $upload_file, 0, 255, 'UTF-8' ),
    ]);

    Flight::dropbox_upload( $file, $upload->upload_file );
}

// json
Flight::json();
