<?php
$user_token = (string) Flight::request()->query['user_token'];
$hub_id = (int) $hub_id;
$hub_status = (string) Flight::request()->query['hub_status'];
$hub_name = (string) Flight::request()->query['hub_name'];

// auth
$user = new \App\Entities\User;
Flight::auth( $user, $user_token );

// hub
$hub = new \App\Entities\Hub;
Flight::select( $hub, [
    ['id', '=', $hub_id], 
    ['user_id', '=', $user->id],
]);

// update status
if( !empty( $hub_status )) {
    Flight::update( $hub, [
        'hub_status' => $hub_status,
    ]);

    // TODO: change code below
    if( $hub_status == 'private' ) {
        $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ));
        $repository->delete( 'user_roles', [['hub_id', '=', $hub->id], ['user_id', '<>', $user->id]] );
    }
}

// update name
if( !empty( $hub_name )) {
    Flight::update( $hub, [
        'hub_name' => $hub_name,
    ]);
}

// json
Flight::json();
