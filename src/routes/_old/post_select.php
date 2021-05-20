<?php
$user_token = (string) Flight::request()->query['user_token'];
$post_id = (int) $post_id;

// auth
$master = Flight::auth( $user_token );

// parent
$post = Flight::post();
Flight::select( $post, [
    ['id', '=', $post_id], 
    ['post_status', '<>', 'trash'],
]);

// repo
$repo = Flight::repo();
Flight::select( $repo, [
    ['id', '=', $post->repo_id], 
    ['repo_status', '<>', 'trash'],
]);

// master role
$master_role = Flight::role();
Flight::select( $master_role, [
    ['user_id', '=', $master->id], 
    ['repo_id', '=', $repo->id], 
]);

// additional checks
if( Flight::empty( 'error' ) and !in_array( $master_role->user_role, ['admin', 'author', 'editor', 'reader'] )) {
    Flight::set( 'error', 'user_role must be admin, author or editor' );
}

/*
$post_tags = new \App\Core\Finder( Flight::get( 'pdo' ));
if( Flight::empty( 'error' )) {
    if( !$post_tags->find( 'App\Core\Meta', 'post_meta', [['post_id', '=', $document->id], ['meta_key', '=', 'post_tag']], 100, 0 )) {
        
        Flight::set( 'e', $post_tags->e );
        Flight::set( 'error', $post_tags->error );
    }
}
*/

/*
// do
$master = Flight::user_auth( $user_token );
$hub = Flight::hub_select( $hub_id, ['private', 'custom'] );
$master_role = Flight::role_select( $hub->id, $master->id, ['admin', 'editor', 'reader'] );
$documents = Flight::documents_select( $hub->id, $limit, $offset );

if( Flight::empty( 'error' )) {
    $output = [];

    foreach( $documents->rows as $row ) {
        $document = [];
        $document['id']           = $row->id;
        $document['create_date']  = $row->create_date;
        $document['update_date']  = $row->update_date;
        $document['user_id']      = $row->user_id;
        $document['post_type']    = $row->post_type;
        $document['post_status']  = $row->post_status;
        $document['post_content'] = $row->post_content;
        array_push( $output, $document );
    }
}
*/







// json
Flight::json([ 'post' => Flight::empty( 'error' ) ? [
    'id'           => $post->id, 
    'create_date'  => $post->create_date, 
    'post_status'  => $post->post_status,
    'post_content' => $post->post_content ] 
    : [],
]);
