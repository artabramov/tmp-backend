<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) Flight::request()->query['hub_id'];
$limit = 10;
$offset = (int) Flight::request()->query['offset'];

// open transaction
Flight::get('pdo')->beginTransaction();

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
    'documents' => Flight::empty( 'error' ) ? $output : [],
]);
