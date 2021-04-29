<?php
$user_token   = (string) Flight::request()->query['user_token'];
$hub_id       = (int) Flight::request()->query['hub_id'];
$post_status  = (string) Flight::request()->query['post_status'];
$post_content = (string) Flight::request()->query['post_content'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$me = new \App\Core\User( Flight::get( 'pdo' ) );
if( !$me->auth( $user_token ) ) {
    Flight::set( 'e', $me->e );
    Flight::set( 'error', $me->error );
}

// select hub
if( !Flight::has_error()) {
    $hub = new \App\Core\Hub( Flight::get( 'pdo' ));
    if( !$hub->get( $hub_id )) {
        Flight::set( 'e', $hub->e );
        Flight::set( 'error', $hub->error );
    }
}

// select role
if( !Flight::has_error()) {
    $role = new \App\Core\Role( Flight::get( 'pdo' ));
    if( !$role->get( $hub->id, $me->id )) {
        Flight::set( 'e', $role->e );
        Flight::set( 'error', $role->error );
    }
}

// insert post
if( !Flight::has_error()) {

    if( $hub->hub_status == 'trash' ) {
        Flight::set( 'error', 'hub is trashed' );

    } elseif( !in_array( $role->user_role, [ 'admin', 'editor'] )) {
        Flight::set( 'error', 'the user must have the role of editor or admin' );

    } else {
        $post = new \App\Core\Post( Flight::get( 'pdo' ));
        
        if( !$post->create( 0, $me->id, $hub->id, 'document', $post_status, $post_content )) {
            Flight::set( 'e', $post->e );
            Flight::set( 'error', $post->error );
        }
    }
}

// close transaction
if( !Flight::has_error()) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// write debug
if( Flight::has_e()) {
    Flight::debug( Flight::get('e') );
}

// send json
Flight::json([ 
    'time'    => Flight::time(),
    'success' => json_encode( !Flight::has_error()),
    'error'   => Flight::has_error() ? Flight::get( 'error' ) : '', 
]);
