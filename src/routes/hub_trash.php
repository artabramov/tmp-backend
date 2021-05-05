<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;

// open transaction
Flight::get('pdo')->beginTransaction();

// auth
$master = new \App\Core\User( Flight::get( 'pdo' ));
Flight::auth( $master, $user_token );

// hub
$hub = new \App\Core\Hub( Flight::get( 'pdo' ));
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['user_id', '=', $master->id],
    ['hub_status', '<>', 'trash'],
]);

// rename
Flight::update( $hub, [
    'update_date' => Flight::time(),
    'hub_status' => 'trash',
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
