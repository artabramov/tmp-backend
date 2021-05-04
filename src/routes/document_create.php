<?php
$user_token   = (string) Flight::request()->query['user_token'];
$hub_id       = (int) Flight::request()->query['hub_id'];
$post_status  = (string) Flight::request()->query['post_status'];
$post_content = (string) Flight::request()->query['post_content'];
$post_tags    = (string) Flight::request()->query['post_meta'];

// open transaction
Flight::get('pdo')->beginTransaction();

// master auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::load( $master, [
    ['user_token', '=', $user_token], 
    ['user_status', '=', 'approved']
]);

// delay for document insert is 5 seconds
if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $master->auth_date ) < 5 ) {
    Flight::set( 'error', 'wait for 5 seconds' );
}

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::load( $hub, [
    ['id', '=', $hub_id], 
    ['hub_status', '=', 'custom'],
]);

// master role
$master_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::load( $master_role, [
    ['user_id', '=', $master->id], 
    ['hub_id', '=', $hub->id], 
]);

// additional checkings
if( Flight::empty( 'error' ) and !in_array( $master_role->user_role, ['admin', 'editor'] ) ) {
    Flight::set( 'error', 'user_role must be admin or editor' );

} elseif( Flight::empty( 'error' ) and !in_array( $post_status, ['draft', 'todo', 'doing', 'done']) ) {
    Flight::set( 'error', 'post_status must be draft, todo, doing or done' );
}

// document insert
$document = new \App\Core\Post( Flight::get( 'pdo' ));
Flight::save( $document, [
    'create_date'  => date( 'Y-m-d H:i:s' ),
    'update_date'  => '0001-01-01 00:00:00',
    'parent_id'    => 0,
    'user_id'      => $master->id,
    'hub_id'       => $hub->id,
    'post_type'    => 'document',
    'post_status'  => $post_status,
    'post_content' => $post_content,
]);

// insert meta
if( !empty( $post_tags )) {
    $tmp = explode( ',', $post_tags );
    foreach( $tmp as $meta_value ) {
        $tag = new \App\Core\Meta( Flight::get( 'pdo' ));
        Flight::save( $tag, [
            'create_date'  => date( 'Y-m-d H:i:s' ),
            'update_date'  => '0001-01-01 00:00:00',
            'user_id'      => $master->id,
            'post_id'      => $document->id,
            'meta_key'     => 'post_tag',
            'meta_value'   => trim( $meta_value ),
        ]);
    }
}

// update auth date
Flight::save( $master, [ 
    'auth_date' => Flight::time()
]);

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
