<?php
$user_token   = (string) Flight::request()->query['user_token'];
$post_id      = (int) Flight::request()->query['post_id']; // parent post
$post_content = (string) Flight::request()->query['post_content'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::auth( $master, $user_token );

// parent post
$document = new \App\Core\Post( Flight::get( 'pdo' ));
Flight::select( $document, [
    ['id', '=', $post_id], 
    ['post_type', '=', 'document'], 
    ['post_status', '<>', 'trash'], 
]);

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::select( $hub, [
    ['id', '=', $document->hub_id], 
    ['hub_status', '<>', 'trash'],
]);

// master role
$master_role = new \App\Core\Role( Flight::get( 'pdo' ));
Flight::select( $master_role, [
    ['user_id', '=', $master->id], 
    ['hub_id', '=', $hub->id], 
]);

// additional checkings
if( Flight::empty( 'error' ) and $hub->hub_status == 'private' and $master_role->user_role != 'admin' ) {
    Flight::set( 'error', 'user_role must be admin' );

} elseif( Flight::empty( 'error' ) and $hub->hub_status == 'custom' and !in_array( $master_role->user_role, ['admin', 'editor', 'commenter'] ) ) {
    Flight::set( 'error', 'user_role must be admin, editor or commenter' );
}

// insert comment
$comment = new \App\Core\Post( Flight::get( 'pdo' ));
Flight::insert( $comment, [
    'create_date'  => date( 'Y-m-d H:i:s' ),
    'update_date'  => '0001-01-01 00:00:00',
    'parent_id'    => $document->id,
    'user_id'      => $master->id,
    'hub_id'       => $hub->id,
    'post_type'    => 'comment',
    'post_status'  => 'inherit',
    'post_content' => $post_content,
    'childs_count' => 0
]);

// update childs_count
Flight::update( $document, [
    'update_date'  => date( 'Y-m-d H:i:s' ),
    'childs_count' => $document->childs_count + 1
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
