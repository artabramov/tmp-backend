<?php

require_once __DIR__ . '/../vendor/autoload.php';

// init
Flight::set( 'e', null );
Flight::set( 'error', '' );
Flight::set( 'pdo', require_once( __DIR__ . "/../src/init/pdo.php" ) );
Flight::set( 'phpmailer', require_once( __DIR__ . "/../src/init/phpmailer.php" ) );
Flight::set( 'monolog', require_once( __DIR__ . "/../src/init/monolog.php" ) );
Flight::set( 'dropbox', require_once( __DIR__ . "/../src/init/dropbox.php" ) );

// ================ MAPPING ================

// error
Flight::map( 'error', function( Throwable $e ) {

    Flight::debug( $e );
    Flight::halt( 500, 'Internal Server Error' );
});

// debug
Flight::map( 'debug', function( Throwable $e ) {

    Flight::get( 'monolog' )->debug( $e->getMessage(), [
        'method'  => Flight::request()->method,
        'url'     => Flight::request()->url,
        'file'    => $e->getFile(),
        'line'    => $e->getLine()
    ]);
});

// generate token
Flight::map( 'token', function() {
    return sha1( date( 'U' )) . bin2hex( random_bytes( 20 ));
});

// generate pass
Flight::map( 'pass', function() {

    $pass_symbols = '0123456789abcdefghijklmnopqrstuvwxyz';
    $pass_len = 6;

    $symbols_length = mb_strlen( $pass_symbols, 'utf-8' ) - 1;
    $user_pass = '';

    for( $i = 0; $i < $pass_len; $i++ ) {
        $user_pass .= $pass_symbols[ random_int( 0, $symbols_length ) ];
    }
    return $user_pass;

});

// het hash
Flight::map( 'hash', function( string $user_pass ) {
    return sha1( $user_pass . '~salt' );
});

// get time
Flight::map( 'time', function() {
    return date( 'Y-m-d H:i:s' );
});

// is flight-variable empty?
Flight::map( 'empty', function( $key ) {
    return empty( Flight::get( $key ));
});


/*
// has error?
Flight::map( 'has_error', function() {
    return Flight::has( 'error' ) and !empty( Flight::get( 'error' ));
});

// has exception?
Flight::map( 'has_e', function() {
    return Flight::has( 'e' ) and !empty( Flight::get( 'e' ));
});
*/


// user register
Flight::map( 'user_register', function( $user_email, $user_token ) {

    $user = new \App\Core\User( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {

        $data = [
            'register_date' => Flight::time(),
            'restore_date'  => '0001-01-01 00:00:00',
            'signin_date'   => '0001-01-01 00:00:00',
            'auth_date'     => '0001-01-01 00:00:00',
            'user_status'   => 'pending',
            'user_token'    => $user_token,
            'user_email'    => $user_email,
            'user_hash'     => '',
        ];
        
        if( !$user->set( $data )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );
        }
    }

    return $user;

});

// user restore (get user by user_email, update user_hash and restore_date)
Flight::map( 'user_restore', function( $user_email, $user_pass ) {

    $user = new \App\Core\User( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {

        if( empty( $user_email )) {
            Flight::set( 'error', 'user_email is empty' );

        } elseif( empty( $user_pass )) {
            Flight::set( 'error', 'user_pass is empty' );

        } elseif( !$user->get( [['user_email', '=', $user_email], ['user_status', '<>', 'trash']] )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );

        } elseif( empty( $user->id )) {
            Flight::set( 'error', 'user not found' );

        } elseif( date( 'U' ) - strtotime( $user->restore_date ) < 30 ) {
            Flight::set( 'error', 'wait for 30 seconds' );

        } elseif( !$user->put( ['restore_date' => Flight::time(), 'user_hash' => Flight::hash( $user_pass ) ] )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );
        }
    }

    return $user;
});

// signin user
Flight::map( 'user_signin', function( $user_email, $user_pass ) {

    $user = new \App\Core\User( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {

        if( empty( $user_email )) {
            Flight::set( 'error', 'user_email is empty' );

        } elseif( empty( $user_pass )) {
            Flight::set( 'error', 'user_pass is empty' );

        } elseif( !$user->get( [['user_email', '=', $user_email], ['user_hash', '=', Flight::hash( $user_pass ) ], ['user_status', '<>', 'trash']] )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );

        } elseif( empty( $user->id )) {
            Flight::set( 'error', 'user not found' );

        } elseif( date( 'U' ) - strtotime( $user->restore_date ) > 60 ) {
            Flight::set( 'error', 'user_pass is expired' );

        } elseif( !$user->put( ['signin_date' => Flight::time(), 'user_status' => 'approved', 'user_hash' => '' ] )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );
        }
    }

    return $user;
});

// send email
Flight::map( 'email', function( $user_email, $user_name, $email_subject, $email_body ) {

    if( empty( Flight::get( 'error' ))) {
        Flight::get('phpmailer')->addAddress( $user_email, $user_name );
        Flight::get('phpmailer')->Subject = $email_subject;
        Flight::get('phpmailer')->Body = $email_body;

        try {
            Flight::get('phpmailer')->send();

        } catch( \Exception $e ) {
            Flight::set( 'e', $e );
            Flight::set( 'error', 'email send error' );
        }
    }
});

//---











// attribute insert
Flight::map( 'attribute_insert', function( $user_id, $attribute_key, $attribute_value, $min_len, $max_len ) {

    $attribute = new \App\Core\Attribute( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {

        if( empty( $attribute_value )) {
            Flight::set( 'error', $attribute_key . ' is empty' );

        } elseif( strlen( $attribute_value ) < $min_len or strlen( $attribute_value ) > $max_len ) {
            Flight::set( 'error', $attribute_key . ' is incorrect' );

        } else {

            $data = [
                'create_date'     => date( 'Y-m-d H:i:s' ),
                'update_date'     => '0001-01-01 00:00:00',
                'user_id'         => $user_id,
                'attribute_key'   => $attribute_key,
                'attribute_value' => $attribute_value,
            ];
        
            if( !$attribute->set( $data )) {
                Flight::set( 'e', $attribute->e );
                Flight::set( 'error', $attribute->error );
            }
        }
    }

    return $attribute;
});

// hub insert
Flight::map( 'hub_insert', function( $user_id, $hub_status, $hub_name ) {

    $hub = new \App\Core\Hub( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {

        $data = [
            'create_date' => date( 'Y-m-d H:i:s' ),
            'update_date' => '0001-01-01 00:00:00',
            'user_id'     => $user_id,
            'hub_status'  => $hub_status,
            'hub_name'    => $hub_name,
        ];
    
        if( !$hub->set( $data )) {
            Flight::set( 'e', $hub->e );
            Flight::set( 'error', $hub->error );
        }
    }

    return $hub;
});

// role insert
Flight::map( 'role_insert', function( $hub_id, $user_id, $user_role ) {

    $role = new \App\Core\Role( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {

        $data = [
            'create_date' => date( 'Y-m-d H:i:s' ),
            'update_date' => '0001-01-01 00:00:00',
            'hub_id'      => $hub_id,
            'user_id'     => $user_id,
            'user_role'   => $user_role,
        ];
    
        if( !$role->set( $data )) {
            Flight::set( 'e', $role->e );
            Flight::set( 'error', $role->error );
        }
    }

    return $role;
});

// user auth
Flight::map( 'user_auth', function( $user_token ) {

    $user = new \App\Core\User( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {
        $user->user_status = 'approved';
        $user->user_token = $user_token;
        $user->load();
    
        if( !empty( $user->error )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );
    
        } elseif( empty( $user->id )) {
            Flight::set( 'error', 'user not found' );

        } else {
            $user->auth_date = date( 'Y-m-d H:i:s' );
            $user->save();

            if( !empty( $user->error )) {
                Flight::set( 'e', $user->e );
                Flight::set( 'error', $user->error );
            }
        }
    }

    return $user;
});

// user select
Flight::map( 'user_select', function( $user_id, $user_status ) {

    $user = new \App\Core\User( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {
        $user->id = $user_id;
        $user->user_status = $user_status;
        $user->load();
    
        if( !empty( $user->error )) {
            Flight::set( 'e', $user->e );
            Flight::set( 'error', $user->error );
    
        } elseif( empty( $user->id )) {
            Flight::set( 'error', 'user not found' );
        }
    }

    return $user;
});

// hub select
Flight::map( 'hub_select', function( $hub_id, $hub_status ) {

    $hub = new \App\Core\Hub( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {
        $hub->id = $hub_id;
        $hub->hub_status = $hub_status;
        $hub->load();
    
        if( !empty( $hub->error )) {
            Flight::set( 'e', $hub->e );
            Flight::set( 'error', $hub->error );
    
        } elseif( empty( $hub->id )) {
            Flight::set( 'error', 'hub not found' );
        }
    }

    return $hub;
});

// role select
Flight::map( 'role_select', function( $hub_id, $user_id, $user_role ) {

    $role = new \App\Core\Role( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {
        $role->hub_id = $hub_id;
        $role->user_id = $user_id;
        $role->user_role = $user_role;
        $role->load();

        if( !empty( $role->error )) {
            Flight::set( 'e', $role->e );
            Flight::set( 'error', $role->error );

        } elseif( empty( $role->id )) {
            Flight::set( 'error', 'role not found' );
        }
    }

    return $role;
});

// role not exists
Flight::map( 'role_absent', function( $hub_id, $user_id ) {

    $role = new \App\Core\Role( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {
        $role->hub_id = $hub_id;
        $role->user_id = $user_id;
        $role->load();

        if( !empty( $role->error )) {
            Flight::set( 'e', $role->e );
            Flight::set( 'error', $role->error );

        } elseif( !empty( $role->id )) {
            Flight::set( 'error', 'role already exists' );
        }
    }

    return $role;
});

// role update
Flight::map( 'role_update', function( $hub_id, $user_id, $user_role ) {

    $role = new \App\Core\Role( Flight::get( 'pdo' ));

    if( empty( Flight::get( 'error' ))) {
        $role->hub_id = $hub_id;
        $role->user_id = $user_id;
        $role->load();

        if( !empty( $role->error )) {
            Flight::set( 'e', $role->e );
            Flight::set( 'error', $role->error );

        } elseif( empty( $role->id )) {
            Flight::set( 'error', 'role not found' );

        } else {
            $role->update_date = date( 'Y-m-d H:i:s' );
            $role->user_role = $user_role;
            $role->save();

            if( !empty( $role->error )) {
                Flight::set( 'e', $role->e );
                Flight::set( 'error', $role->error );
            }
        }
    }

    return $role;
});

//================ ROUTES ================

Flight::route( 'GET /', function() {
    $pdo = require __DIR__ . "/../src/init/pdo.php";

    $attribute = new \App\Core\Attribute( $pdo );
    $attribute->set(['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 1, 'attribute_key' => 'key', 'attribute_value' => 'value']);
    //$data = ['register_date' => '0001-01-01 00:00:00', 'restore_date' => '0001-01-01 00:00:00', 'signin_date' => '0001-01-01 00:00:00', 'auth_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_hash' => 'hash'];
    //$res = $user->put(['user_hash' => 'hash']);

    /*
    $role = new \App\Core\Role( $pdo );
    $role->create_date = '0001-01-01 00:00:00';
    $role->update_date = '0001-01-01 00:00:00';
    $role->hub_id = 0;
    $role->user_id = 1;
    $role->user_role = 'role';
    $role->save();
    */


    /*
    $user = new \App\Core\User( $pdo );
    $user->register_date = '0001-01-01 00:00:00';
    $user->restore_date  = '0001-01-01 00:00:00';
    $user->signin_date   = '0001-01-01 00:00:00';
    $user->auth_date     = '0001-01-01 00:00:00';
    $user->user_status   = 'status';
    $user->user_token    = 'token2';
    $user->user_email    = 'noreply2@noreply.no';
    $user->user_hash     = 'hash';
    $user->save();
    */

    //$result = $user->set( ['user_status' => 'status', 'user_token' => 'token', 'user_email' => 'noreply@noreply.ru', 'user_hash' => 'hash' ] );
    $a = 1;

    //$meta->set(['user_id' => 1, 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value', '_key' => 'value' ]);
    //

    //$pdo->query( "TRUNCATE TABLE post_meta;" );
    //$pdo->query( "INSERT INTO post_meta VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 1, 1, 'key', 'value');" );

    //$meta->put(['meta_value' => 'value 2']);
});

// user register
Flight::route( 'POST /user', function() {
    require_once( '../src/routes/user_register.php' );
});

// user restore
Flight::route( 'GET /pass', function() {
    require_once( '../src/routes/user_restore.php' );
});

// user signin
Flight::route( 'POST /pass', function() {
    require_once( '../src/routes/user_signin.php' );
});

// user signout
Flight::route( 'PUT /token', function() {
    require_once( '../src/routes/user_signout.php' );
});

// user select
Flight::route( 'GET /user/@user_id', function( $user_id ) {
    require_once( '../src/routes/user_select.php' );
});

// hub create
Flight::route( 'POST /hub', function() {
    require_once( '../src/routes/hub_create.php' );
});

// get documents
Flight::route( 'GET /documents', function() {
    require_once( '../src/routes/documents_select.php' );
});

// role invite
Flight::route( 'POST /role', function() {
    require_once( '../src/routes/role_invite.php' );
});

// role approve
Flight::route( 'GET /role', function() {
    require_once( '../src/routes/role_approve.php' );
});

// change role
Flight::route( 'PUT /role', function() {
    require_once( '../src/routes/role_update.php' );
});

// create document
Flight::route( 'POST /document', function() {
    require_once( '../src/routes/document_create.php' );
});

// create comment
Flight::route( 'POST /comment', function() {
    require_once( '../src/routes/comment_create.php' );
});






// Post file
Flight::route( 'POST /upload', function() {

    $upload = Flight::request()->files->upload;

    // Local file
    $dropboxFile = new DropboxFile($upload['tmp_name']); 

    // Download the File
    //$dropbox->download("/Software Sharing/Vessel Usernames.xlsx", $dropboxFile);

    // Upload the file
    Flight::get('dropbox')->simpleUpload($dropboxFile, "/temp/temp.jpg", ['autorename' => true]);

    Flight::json([ 
        'success' => 'unknown', 
        'error' => 'unknown'
    ]);
});

//================ START ================

Flight::start();
