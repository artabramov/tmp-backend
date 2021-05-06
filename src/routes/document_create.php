<?php
$user_token   = (string) Flight::request()->query['user_token'];
$hub_id       = (int) Flight::request()->query['hub_id'];
$post_status  = (string) Flight::request()->query['post_status'];
$post_content = (string) Flight::request()->query['post_content'];
$post_tags    = (string) Flight::request()->query['post_tags'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::auth( $master, $user_token );

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '<>', 'trash'],
]);

// master role
$master_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::select( $master_role, [
    ['user_id', '=', $master->id], 
    ['hub_id', '=', $hub->id], 
]);


// document insert
$document = new \App\Core\Post( Flight::get( 'pdo' ));
Flight::insert( $document, [
    'create_date'  => date( 'Y-m-d H:i:s' ),
    'update_date'  => '0001-01-01 00:00:00',
    'parent_id'    => 0,
    'user_id'      => $master->id,
    'hub_id'       => $hub->id,
    'post_type'    => 'document',
    'post_status'  => $post_status,
    'post_content' => $post_content,
]);

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

// close transaction
if( Flight::empty( 'error' )) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// debug
if( !Flight::empty( 'e' )) {
    Flight::debug( Flight::get('e') );
}

// json
Flight::json([ 
    'time'    => Flight::time(),
    'success' => Flight::empty( 'error' ) ? 'true' : 'false',
    'error'   => Flight::empty( 'error' ) ? '' : Flight::get( 'error' ), 
]);
