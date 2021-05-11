<?php
$user_token = (string) Flight::request()->query['user_token'];
$repo_id = (int) Flight::request()->query['repo_id'];
$parent_id = (int) Flight::request()->query['parent_id'];
$post_status = (string) Flight::request()->query['post_status'];
$post_content = (string) Flight::request()->query['post_content'];
$post_tags = (string) Flight::request()->query['post_tags'];

// auth
$master = Flight::auth( $user_token );

// document
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

    } elseif( Flight::empty( 'error' ) and !in_array( $post_status, ['todo', 'doing', 'done', 'draft']) ) {
        Flight::set( 'error', 'post_status must be todo, doing, done or draft' );
    }

    // post insert
    $post = Flight::post();
    Flight::insert( $post, [
        'user_id'      => $master->id,
        'repo_id'      => $repo->id,
        'post_status'  => $post_status,
        'post_content' => $post_content,
    ]);

// comment
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

    } elseif( Flight::empty( 'error' ) and !in_array( $parent->post_status, ['todo', 'doing', 'done', 'draft']) ) {
        Flight::set( 'error', 'parent post_status must be todo, doing, done or draft' );
    }

    // post insert
    $post = Flight::post();
    Flight::insert( $post, [
        'parent_id' => $parent->id,
        'user_id' => $master->id,
        'repo_id' => $repo->id,
        //'post_status' => $post_status,
        'post_content' => $post_content,
    ]);




}







/*
// parent post
if( !empty( $parent_id )) {

    $parent = Flight::post();
    Flight::select( $parent, [
        ['id', '=', $parent_id],
        ['repo_id', '=', $repo_id],
        ['post_status', '<>', 'trash']
    ]);
}
*/



/*
$data = [
    'user_id'      => $master->id,
    'repo_id'      => $repo->id,
    'post_status'  => $post_status,
    'post_content' => $post_content,
];

if( !empty( $parent )) {
    $data['parent_id'] = $parent->id;
}
*/



/*
// upload the files
foreach( Flight::request()->files as $file ) {

    $tmp = explode( '.', $file['name'] );
    $ext = count( $tmp ) > 0 ? '.' . end( $tmp ) : '';
    $upload_mime = mime_content_type( $file['tmp_name'] );
    $upload_name = $file['name'];
    $upload_file = '/echidna/' . $master->id . '/' . sha1( Flight::timestamp() . $file['name'] ) . $ext;

    $upload = new \App\Core\Upload( Flight::get( 'pdo' ));
    Flight::insert( $upload, [
        'create_date'   => date( 'Y-m-d H:i:s' ),
        'update_date'   => '0001-01-01 00:00:00',
        'user_id'       => $master->id,
        'post_id'       => $document->id,
        'upload_status' => 'common',
        'upload_name'   => mb_substr( $upload_name, 0, 255, 'UTF-8' ),
        'upload_mime'   => mb_substr( $upload_mime, 0, 255, 'UTF-8' ),
        'upload_size'   => $file['size'],
        'upload_file'   => mb_substr( $upload_file, 0, 255, 'UTF-8' ),
    ]);

    Flight::upload( $file, $upload->upload_file );
}


// additional checkings
if( Flight::empty( 'error' ) and $hub->hub_status == 'private' and $master_role->user_role != 'admin' ) {
    Flight::set( 'error', 'user_role must be admin' );

} elseif( Flight::empty( 'error' ) and $hub->hub_status == 'custom' and !in_array( $master_role->user_role, ['admin', 'editor'] ) ) {
    Flight::set( 'error', 'user_role must be admin or editor' );

} elseif( Flight::empty( 'error' ) and !in_array( $post_status, ['draft', 'todo', 'doing', 'done']) ) {
    Flight::set( 'error', 'post_status must be draft, todo, doing or done' );
}

// insert meta
if( !empty( $post_tags )) {

    $tmp = explode( ',', $post_tags );
    foreach( $tmp as $meta_value ) {

        $meta = new \App\Core\Meta( Flight::get( 'pdo' ));
        Flight::insert( $meta, [
            'create_date'  => date( 'Y-m-d H:i:s' ),
            'update_date'  => '0001-01-01 00:00:00',
            'user_id'      => $master->id,
            'post_id'      => $document->id,
            'meta_key'     => 'post_tag',
            'meta_value'   => trim( mb_strtolower( $meta_value, 'UTF-8' )),
        ]);
    }
}
*/

// json
Flight::json();
