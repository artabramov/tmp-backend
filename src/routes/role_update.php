<?php
$user_token = Flight::request()->query['user_token'];
$hub_id = Flight::request()->query['hub_id'];
$user_id = Flight::request()->query['user_id'];
$user_role = Flight::request()->query['user_role'];

// open transaction
Flight::get('pdo')->beginTransaction();

// auth by user_token (master)
$master = new \App\Core\User( Flight::get( 'pdo' ));

if( empty( $user_token )) {
    Flight::set( 'error', 'user_token is empty' );

} else {
    $master->load( [['user_token', '=', $user_token]] );

    if( empty( $master->id )) {
        Flight::set( 'error', 'user_token not found' );
    
    } elseif( $master->user_status != 'approved' ) {
        Flight::set('error', 'user_token is not approved');
    
    } elseif( !empty( $master->error )) {
        Flight::set( 'e', $master->e );
        Flight::set( 'error', $master->error );
    }
}

// get user by user_id (slave)
if( empty( Flight::get( 'error' ))) {

    if( empty( $user_id )) {
        Flight::set( 'error', 'user_id is empty' );

    } else {
        $slave = new \App\Core\User( Flight::get( 'pdo' ));
        $slave->load( [['id', '=', $user_id]] );

        if( !empty( $slave->error )) {
            Flight::set( 'e', $slave->e );
            Flight::set( 'error', $slave->error );

        } elseif( empty( $slave->id )) {
            Flight::set( 'error', 'user_id not found' );
        
        } elseif( $slave->user_status != 'approved' ) {
            Flight::set( 'error', 'user_status is not approved' );
        }
    }
}

// get hub by hub_id
if( empty( Flight::get( 'error' ))) {

    if( empty( $hub_id )) {
        Flight::set( 'error', 'hub_id is empty' );

    } else {
        $hub = new \App\Core\Hub( Flight::get( 'pdo' ));
        $hub->load( [['id', '=', $hub_id]] );

        if( !empty( $hub->error )) {
            Flight::set( 'e', $hub->e );
            Flight::set( 'error', $hub->error );

        } elseif( empty( $hub->id )) {
            Flight::set( 'error', 'hub_id not found' );

        } elseif( $hub->hub_status != 'custom' ) {
            Flight::set( 'error', 'hub_status must be custom' );
        }
    }
}

// get master role
if( empty( Flight::get( 'error' ))) {
    $master_role = new \App\Core\Role( Flight::get( 'pdo' ));
    $master_role->load( [['hub_id', '=', $hub->id], ['user_id', '=', $master->id]] );

    if( !empty( $master_role->error )) {
        Flight::set( 'e', $master_role->e );
        Flight::set( 'error', $master_role->error );

    } elseif( empty( $master_role->id )) {
        Flight::set( 'error', 'user_role not found' );

    } elseif( $master_role->user_role != 'admin' ) {
        Flight::set( 'error', 'user_role must be an admin' );
    }
}

// get slave role
if( empty( Flight::get( 'error' ))) {
    $slave_role = new \App\Core\Role( Flight::get( 'pdo' ));
    $slave_role->load( [['hub_id', '=', $hub->id], ['user_id', '=', $slave->id]] );

    if( !empty( $slave_role->error )) {
        Flight::set( 'e', $slave_role->e );
        Flight::set( 'error', $slave_role->error );

    } elseif( empty( $slave_role->id )) {
        Flight::set( 'error', 'user_role not found' );

    } elseif( !in_array( $slave_role->user_role, ['editor', 'reader'] ) ) {
        Flight::set( 'error', 'user_role must be editor or reader' );
    }
}

// update slave role to editor/reader
if( empty( Flight::get( 'error' ))) {

    if( empty( $user_role )) {
        Flight::set( 'error', 'user_role is empty' );

    } elseif( !in_array( $user_role, ['editor', 'reader'] )) {
        Flight::set( 'error', 'user_role is incorrect' );

    } else {
        $slave_role->update_date = date( 'Y-m-d H:i:s' );
        $slave_role->user_role = $user_role;

        if( !$slave_role->save()) {
            Flight::set( 'e', $slave_role->e );
            Flight::set( 'error', $slave_role->error );
        }
    }
}

// update auth_date
if( empty( Flight::get( 'error' ))) {
    $master->auth_date = date( 'Y-m-d H:i:s' );

    if( !$master->save()) {
        Flight::set( 'e', $master->e );
        Flight::set( 'error', $master->error );
    }
}

// close transaction
if( empty( Flight::get( 'error' ))) {
    Flight::get( 'pdo' )->commit();

} else {
    Flight::get( 'pdo' )->rollBack();
}

// debug
if( !empty( Flight::get( 'e' ))) {
    Flight::debug( Flight::get('e') );
}

// json
Flight::json([ 
    'time'    => date( 'Y-m-d H:i:s' ),
    'success' => empty( Flight::get( 'error' )) ? 'true' : 'false',
    'error'   => !empty( Flight::get( 'error' )) ? Flight::get( 'error' ) : '', 
]);
