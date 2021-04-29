<?php
$user_token   = (string) Flight::request()->query['user_token'];
$post_id      = (int) Flight::request()->query['post_id']; // parent post
$post_content = (string) Flight::request()->query['post_content'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$me = new \App\Core\User( Flight::get( 'pdo' ) );
if( !$me->auth( $user_token ) ) {
    Flight::set( 'e', $me->e );
    Flight::set( 'error', $me->error );
}

// parent post
if( !Flight::has_error()) {
    $document = new \App\Core\Post( Flight::get( 'pdo' ));
    if( !$document->get( $post_id )) {
        Flight::set( 'e', $document->e );
        Flight::set( 'error', $document->error );
    }
}

// hub
if( !Flight::has_error()) {
    $hub = new \App\Core\Hub( Flight::get( 'pdo' ));
    if( !$hub->get( $document->hub_id )) {
        Flight::set( 'e', $hub->e );
        Flight::set( 'error', $hub->error );
    }
}

// select role
if( !Flight::has_error()) {
    $role = new \App\Core\Role( Flight::get( 'pdo' ));
    if( !$role->get( $document->hub_id, $me->id )) {
        Flight::set( 'e', $role->e );
        Flight::set( 'error', $role->error );
    }
}

// insert comment
if( !Flight::has_error()) {

    if( $hub->hub_status == 'trash' ) {
        Flight::set( 'error', 'hub is trashed' );

    } elseif( $document->post_type != 'document' ) {
        Flight::set( 'error', 'parent must be a document type' );

    } elseif( !in_array( $role->user_role, [ 'admin', 'editor'] )) {
        Flight::set( 'error', 'the user must have the role of editor or admin' );

    } else {
        $comment = new \App\Core\Post( Flight::get( 'pdo' ));
        
        if( !$comment->create( $document->id, $me->id, $hub->id, 'comment', 'inherit', $post_content )) {
            Flight::set( 'e', $comment->e );
            Flight::set( 'error', $comment->error );
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
