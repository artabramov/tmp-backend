<?php
require_once __DIR__ . '/../vendor/autoload.php';

define( 'UPLOADS_LIMIT', 1024 * 1024 * 2 );

// init
Flight::set( 'error', '' );
Flight::set( 'pdo', require_once( __DIR__ . "/../src/init/pdo.php" ) );
Flight::set( 'phpmailer', require_once( __DIR__ . "/../src/init/phpmailer.php" ) );
Flight::set( 'monolog', require_once( __DIR__ . "/../src/init/monolog.php" ) );
//Flight::set( 'dropbox', require_once( __DIR__ . "/../src/init/dropbox.php" ) );

// data mapper and sequence
$repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ));
Flight::set( 'mapper', new \artabramov\Echidna\Mapper( $repository ) );
Flight::set( 'sequence', new \artabramov\Echidna\Sequence( $repository ) );
Flight::set( 'time', new \artabramov\Echidna\Time( $repository ) );

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
Flight::map( 'upload', function( $keys, $user_id, $comment_id ) {

    if( Flight::empty( 'error' )) {

        $path = __DIR__ . '/uploads/' . date('Y-m-d');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $storage = new \Upload\Storage\FileSystem( $path );
        $file = new \Upload\File( $keys[0], $storage );
        
        // Optionally you can rename the file on upload
        $new_filename = $user_id . uniqid();
        $file->setName( $new_filename );
        
        // Validate file upload
        // MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
        $file->addValidations(array(

            //You can also add multi mimetype validation
            new \Upload\Validation\Mimetype(array('image/png', 'image/gif', 'image/jpeg')),
        
            // Ensure file is no larger than 5M (use "B", "K", M", or "G")
            new \Upload\Validation\Size('5M')
        ));
        
        // Access data about the file that has been uploaded
        $data = array(
            'original_name' => Flight::request()->files[$keys[0]]['name'],
            '_name'     => $file->getName(),
            'name'       => $file->getNameWithExtension(),
            'extension'  => $file->getExtension(),
            'mime'       => $file->getMimetype(),
            'size'       => $file->getSize(),
            'md5'        => $file->getMd5(),
            'dimensions' => $file->getDimensions()
        );
        
        // Try to upload file
        try {
            $file->upload();

            $upload = new \App\Entities\Upload;
            Flight::insert( $upload, [
                'user_id' => $user_id,
                'comment_id' => $comment_id,
                'upload_name' => $data['original_name'],
                'upload_mime' => $data['mime'],
                'upload_size' => $data['size'],
                'upload_file' => $path . '/' . $data['name'],
            ]);

            if( !Flight::empty( 'error' )) {
                unlink( $path . '/' . $data['name'] );
            }

        } catch (\Exception $e) {
            //Flight::set( 'e', $e );
            $errors = $file->getErrors();
            Flight::set( 'error', strtolower($errors[0]) );
        }
    }
});

// insert entity
Flight::map( 'insert', function( $entity, $data ) {

    if( Flight::empty( 'error' )) {
        Flight::get('mapper')->insert( $entity, $data );

        if( !empty( Flight::get('mapper')->error )) {
            Flight::set( 'error', Flight::get('mapper')->error );
        }
    }
});

// update entity
Flight::map( 'update', function( $entity, $data ) {

    if( Flight::empty( 'error' )) {
        Flight::get('mapper')->update( $entity, $data );

        if( !empty( Flight::get('mapper')->error )) {
            Flight::set( 'error', Flight::get('mapper')->error );
        }
    }
});

// select entity
Flight::map( 'select', function( $entity, $args ) {

    if( Flight::empty( 'error' )) {
        Flight::get('mapper')->select( $entity, $args );

        if( !empty( Flight::get('mapper')->error )) {
            Flight::set( 'error', Flight::get('mapper')->error );
        }
    }
});

// delete entity
Flight::map( 'delete', function( $entity ) {

    if( Flight::empty( 'error' )) {
        Flight::get('mapper')->delete( $entity );

        if( !empty( Flight::get('mapper')->error )) {
            Flight::set( 'error', Flight::get('mapper')->error );
        }
    }
});

// entity exists
Flight::map( 'exists', function( $entity, $args ) {
    return Flight::get('mapper')->exists( $entity, $args );
});

// get time
Flight::map( 'time', function() {
    return Flight::get('time')->time;
});

// auth
Flight::map( 'auth', function( $user, $user_token ) {

    Flight::select( $user, [
        ['user_token', '=', $user_token], 
        ['user_status', 'IN', ['approved', 'premium']]   
    ]);

    $time = Flight::time();
    if( Flight::empty( 'error' ) and strtotime( $time ) - strtotime( $user->restore_date ) > 60 * 60 * 24 * 30 ) {
        Flight::set( 'error', 'user_token is expired' );
    }
});

// ==== FILTERING ====

// before route
Flight::before('start', function( &$params, &$output ) {
    Flight::get('mapper')->start();
});

// after route
Flight::after('stop', function( &$params, &$output ) {
    Flight::get('mapper')->stop();
});

// json
Flight::before('json', function( &$params, &$output ) {
    $params[0]['time']    = Flight::time();
    $params[0]['success'] = Flight::empty( 'error' ) ? 'true' : 'false';
    $params[0]['error']   = Flight::empty( 'error' ) ? '' : Flight::get( 'error' );
});

//================ ROUTES ================

// test
Flight::route( 'GET /test', function() {

    // select
    //$user = new \App\Entities\User;
    //Flight::select( $user, [['id', '=', 1]] );

    // insert
    //$meta = new \App\Entities\Meta;
    //Flight::insert( $meta, ['user_id' => 1, 'parent_type' => 'users', 'parent_id' => 1, 'meta_key' => 'user_tag', 'meta_value' => 'tag !'] );

    // update
    //$meta = new \App\Entities\Meta;
    //Flight::select( $meta, [['id', '=', 1]] );
    //Flight::update( $meta, ['meta_value' => 'trash'] );

    // delete
    //$meta = new \App\Entities\Meta;
    //Flight::select( $meta, [['id', '=', 3]] );
    //Flight::delete( $meta );

    // time
    $time = Flight::time();

    // exists
    //$user = new \App\Entities\User;
    //$exists = Flight::exists( $user, [['id', '=', 3]] );

    // sequence
    $sequence = Flight::get('sequence');
    $query1 = $sequence->select( ['parent_id'], 'meta', [['parent_type', '=', 'users'], ['meta_key', '=', 'user_tag'], ['meta_value', '=', 'user_value']] );
    $query2 = $sequence->select( ['*'], 'users', [['user_status', '<>', 'trash'], ['id', 'IN', $query1]] );
    $sequence->execute( $query2, new \App\Entities\User );
    $count = $sequence->count( 'meta', [['parent_type', '=', 'users']] );

    /*
    // transaction
    $meta = new \App\Entities\User;
    Flight::insert( $user, ['user_status' => 'pending', 'user_token' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'user_email' => 'aa@zz.zz', 'user_name' => 'zzzzz'] );
    Flight::insert( $user, ['user_status' => 'pending', 'user_token' => 'baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'user_email' => 'ba@zz.zz', 'user_name' => 'zzzzz'] );
    $error = Flight::get('error');
    */


    $a = 1;

});

// webapp
Flight::route( 'GET /', function() {
    $page = Flight::request()->query['page'];
    Flight::render( __DIR__ . '/webapp/index.php', array( 'page' => $page ));
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

// user auth
Flight::route( 'GET /auth', function() {
    require_once( '../src/routes/user_auth.php' );
});

// user rename
Flight::route( 'PUT /user', function() {
    require_once( '../src/routes/user_update.php' );
});

// hub create
Flight::route( 'POST /hub', function() {
    require_once( '../src/routes/hub_insert.php' );
});

// hub update
Flight::route( 'PUT /hub/@hub_id', function( $hub_id ) {
    require_once( '../src/routes/hub_update.php' );
});

// hub delete
Flight::route( 'DELETE /hub/@hub_id', function( $hub_id ) {
    require_once( '../src/routes/hub_delete.php' );
});

// hubs list
Flight::route( 'GET /hubs', function() {
    require_once( '../src/routes/hub_sequence.php' );
});

// role invite
Flight::route( 'POST /role', function() {
    require_once( '../src/routes/role_insert.php' );
});

// role confirm
Flight::route( 'GET /role', function() {
    require_once( '../src/routes/role_confirm.php' );
});

// role update
Flight::route( 'PUT /role', function() {
    require_once( '../src/routes/role_update.php' );
});

// role delete
Flight::route( 'DELETE /role', function() {
    require_once( '../src/routes/role_delete.php' );
});

// post insert
Flight::route( 'POST /document', function() {
    require_once( '../src/routes/document_insert.php' );
});

// update document
Flight::route( 'PUT /document/@post_id', function( $post_id ) {
    require_once( '../src/routes/document_update.php' );
});

// delete document
Flight::route( 'DELETE /document/@post_id', function( $post_id ) {
    require_once( '../src/routes/document_delete.php' );
});

// comment insert
Flight::route( 'POST /comment', function() {
    require_once( '../src/routes/comment_insert.php' );
});

// comment delete
Flight::route( 'DELETE /comment/@comment_id', function( $comment_id ) {
    require_once( '../src/routes/comment_delete.php' );
});

// insert upload and file
Flight::route( 'POST /upload', function() {
    require_once( '../src/routes/upload_insert.php' );
});

// upload delete
Flight::route( 'DELETE /upload/@upload_id', function( $upload_id ) {
    require_once( '../src/routes/upload_delete.php' );
});

// upload update
Flight::route( 'PUT /upload/@upload_id', function( $upload_id ) {
    require_once( '../src/routes/upload_update.php' );
});

/*
// select the document
Flight::route( 'GET /post/@post_id', function( $post_id ) {
    require_once( '../src/routes/post_select.php' );
});
*/

/*
// documents (!) select
Flight::route( 'GET /documents', function() {
    require_once( '../src/routes/documents_select.php' );
});
*/

/*
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
*/

//================ START ================

Flight::start();
