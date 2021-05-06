<?php
$user_token = (string) Flight::request()->query['user_token'];
$post_id = (int) $post_id;

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::auth( $master, $user_token );

// document
$document = new \App\Core\Post( Flight::get( 'pdo' ));
Flight::select( $document, [
    ['id', '=', $post_id], 
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


$post_tags = new \App\Core\Finder( Flight::get( 'pdo' ));
if( Flight::empty( 'error' )) {
    if( !$post_tags->find( 'App\Core\Meta', 'post_meta', [['post_id', '=', $document->id], ['meta_key', '=', 'post_tag']], 100, 0 )) {
        
        Flight::set( 'e', $post_tags->e );
        Flight::set( 'error', $post_tags->error );
    }
}





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
