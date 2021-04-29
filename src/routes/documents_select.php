<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$me = new \App\Core\User( Flight::get( 'pdo' ));
if( !$me->auth( $user_token )) {
    Flight::set( 'e', $me->e );
    Flight::set( 'error', $me->error );
}

// hub
if( !Flight::has_error()) {
    $hub = new \App\Core\Hub( Flight::get( 'pdo' ));
    if( !$hub->get( $hub_id )) {
        Flight::set( 'e', $hub->e );
        Flight::set( 'error', $hub->error );
    }
}

// role
if( !Flight::has_error()) {
    $my_role = new \App\Core\Role( Flight::get( 'pdo' ));
    if( !$my_role->get( $hub->id, $me->id )) {
        Flight::set( 'e', $my_role->e );
        Flight::set( 'error', $my_role->error );
    }
}

// posts
if( !Flight::has_error()) {
    $posts = new \App\Core\Collector( Flight::get( 'pdo' ));
    if( !$posts->get( 'App\Core\Post', 'posts', [['hub_id', '=', $hub->id]], 100, 0 )) {
        Flight::set( 'e', $my_role->e );
        Flight::set( 'error', $my_role->error );
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
    'hub'    => Flight::has_error() ? [] : [ 
        'id'          => $hub->id, 
        'create_date' => $hub->create_date, 
        'update_date' => $hub->update_date, 
        'hub_status'  => $hub->hub_status,
        'hub_name'    => $hub->hub_name ],
    'posts' => Flight::has_error() ? [] : $posts->pull(),
]);
