<?php
require_once __DIR__ . '/../vendor/autoload.php';

use \Kunnu\Dropbox\Dropbox;
use \Kunnu\Dropbox\DropboxApp;
use \Kunnu\Dropbox\DropboxFile;

// init
Flight::set( 'e', null );
Flight::set( 'error', '' );
Flight::set( 'pdo', require_once( __DIR__ . "/../src/init/pdo.php" ) );
Flight::set( 'phpmailer', require_once( __DIR__ . "/../src/init/phpmailer.php" ) );
Flight::set( 'monolog', require_once( __DIR__ . "/../src/init/monolog.php" ) );
Flight::set( 'dropbox', require_once( __DIR__ . "/../src/init/dropbox.php" ) );

// ================ MAPPING ================

// is flight-variable empty?
Flight::map( 'empty', function( $key ) {
    return empty( Flight::get( $key ));
});

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

    $pass_symbols = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $pass_len = 8;

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

// TODO: rename to datetime()
Flight::map( 'time', function() {
    return date( 'Y-m-d H:i:s' );
});

// get timestamp
Flight::map( 'timestamp', function() {
    return date( 'U' );
});

// send email
Flight::map( 'email', function( $user_email, $user_name, $email_subject, $email_body ) {

    if( Flight::empty( 'error' )) {

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

// upload the file
Flight::map( 'upload', function( $file, $upload_file ) {

    if( Flight::empty( 'error' )) {

        //Configure Dropbox Application
        $app_key = 'ir3ndpahsbnyru0';
        $app_secret = '1bwvj9amy1ai55f';
        $access_token = 'WSpPvkMfiVEAAAAAAAAAAe-DJD3Ot3stp7ci2Mpvi_hZhvdbYJjSrtfYTdKPD3Rm';
        $dropbox_app = new DropboxApp( $app_key, $app_secret, $access_token );

        try{
            //Configure Dropbox service
            $dropbox = new Dropbox( $dropbox_app );

            // create Dropbox file
            $dropbox_file = new DropboxFile( $file['tmp_name'] ); 

            // dropbox upload
            $dropbox_upload = $dropbox->simpleUpload( $dropbox_file, $upload_file, ['autorename' => true] );

        } catch( \Exception $e ) {
            Flight::set( 'e', $e );
            Flight::set( 'error', 'upload error' );
        }
    }
});

// auth
Flight::map( 'auth', function( $user, $user_token ) {

    Flight::select( $user, [
        ['user_token', '=', $user_token], 
        ['user_status', '=', 'approved']
    ]);

    $restore_date = new \App\Core\Param( Flight::get( 'pdo' ));
    Flight::select( $restore_date, [
        ['user_id', '=', $user->id], 
        ['param_key', '=', 'restore_date']
    ]);

    $auth_date = new \App\Core\Param( Flight::get( 'pdo' ));
    Flight::select( $auth_date, [
        ['user_id', '=', $user->id], 
        ['param_key', '=', 'auth_date']
    ]);
    
    if( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $auth_date->param_value ) < 1 ) {
        Flight::set( 'error', 'wait for 1 second' );
    
    } elseif( Flight::empty( 'error' ) and date( 'U' ) - strtotime( $restore_date->param_value ) > 24 * 60 * 60 ) {
        Flight::set( 'error', 'user_token is expired' );
    }
    
    Flight::update( $auth_date, [
        'param_value' => Flight::time()
    ]);
});

// insert
Flight::map( 'insert', function( $instance, $data ) {

    if( Flight::empty( 'error' )) {
        if( !$instance->set( $data )) {
            Flight::set( 'e', $instance->e );
            Flight::set( 'error', $instance->error );
        }
    }
});

// update
Flight::map( 'update', function( $instance, $data ) {

    if( Flight::empty( 'error' )) {
        if( !$instance->put( $data )) {
            Flight::set( 'e', $instance->e );
            Flight::set( 'error', $instance->error );
        }
    }
});

// select
Flight::map( 'select', function( $instance, $args ) {

    if( Flight::empty( 'error' )) {
        if( !$instance->get( $args )) {
            Flight::set( 'e', $instance->e );
            Flight::set( 'error', $instance->error );
        }
    }
});

// delete
Flight::map( 'delete', function( $instance ) {

    if( Flight::empty( 'error' )) {
        if( !$instance->del()) {
            Flight::set( 'e', $instance->e );
            Flight::set( 'error', $instance->error );
        }
    }
});

/*
// documents (!) select
Flight::map( 'documents_select', function( $hub_id, $limit, $offset ) {

    $posts = new \App\Core\Collector( Flight::get( 'pdo' ));

    if( Flight::empty( 'error' )) {

        if( !$posts->get( 'App\Core\Post', 'posts', [['hub_id', '=', $hub_id]], $limit, $offset )) {
            Flight::set( 'e', $posts->e );
            Flight::set( 'error', $posts->error );
        }
    }

    return $posts;
});
*/

//================ ROUTES ================

/*
Flight::route( 'GET /', function() {
    $pdo = require __DIR__ . "/../src/init/pdo.php";
    $attribute = new \App\Core\Attribute( $pdo );
    $attribute->set(['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 1, 'attribute_key' => 'key', 'attribute_value' => 'value']);
    $a = 1;
});
*/

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

// user rename
Flight::route( 'PUT /user', function() {
    require_once( '../src/routes/user_rename.php' );
});

// hub create
Flight::route( 'POST /hub', function() {
    require_once( '../src/routes/hub_create.php' );
});

// hub rename
Flight::route( 'PUT /hub/@hub_id', function( $hub_id ) {
    require_once( '../src/routes/hub_update.php' );
});

// hub trash
Flight::route( 'DELETE /hub/@hub_id', function( $hub_id ) {
    require_once( '../src/routes/hub_trash.php' );
});

// role invite
Flight::route( 'POST /role', function() {
    require_once( '../src/routes/role_invite.php' );
});

// role approve
Flight::route( 'GET /role', function() {
    require_once( '../src/routes/role_approve.php' );
});

// role update
Flight::route( 'PUT /role', function() {
    require_once( '../src/routes/role_update.php' );
});

// role delete
Flight::route( 'DELETE /role', function() {
    require_once( '../src/routes/role_delete.php' );
});

// create document
Flight::route( 'POST /document', function() {
    require_once( '../src/routes/document_create.php' );
});

// select the document
Flight::route( 'GET /document/@post_id', function( $post_id ) {
    require_once( '../src/routes/document_select.php' );
});



// documents (!) select
Flight::route( 'GET /documents', function() {
    require_once( '../src/routes/documents_select.php' );
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

// ---- META ----

Flight::route( 'POST /meta', function() {
    require_once( '../src/routes/meta_post.php' );
});

//================ START ================

Flight::start();
