<?php
require_once __DIR__ . '/../vendor/autoload.php';

define( 'URI', 'http://project.local' );
define( 'UPLOADS_LIMIT', 1024 * 1024 * 2 );

// init
Flight::set( 'e', null );
Flight::set( 'error', '' );
Flight::set( 'pdo', require_once( __DIR__ . "/../src/init/pdo.php" ) );
Flight::set( 'phpmailer', require_once( __DIR__ . "/../src/init/phpmailer.php" ) );
Flight::set( 'monolog', require_once( __DIR__ . "/../src/init/monolog.php" ) );
//Flight::set( 'dropbox', require_once( __DIR__ . "/../src/init/dropbox.php" ) );

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
        $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
        $mapper = new \artabramov\Echidna\Mapper( $repository );
        $mapper->insert( $entity, $data );

        if( !empty( $mapper->error )) {
            Flight::set( 'e', $repository->e );
            Flight::set( 'error', $mapper->error );
        }
    }
});

// update entity
Flight::map( 'update', function( $entity, $data ) {

    if( Flight::empty( 'error' )) {
        $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
        $mapper = new \artabramov\Echidna\Mapper( $repository );
        $mapper->update( $entity, $data );

        if( !empty( $mapper->error )) {
            Flight::set( 'e', $repository->e );
            Flight::set( 'error', $mapper->error );
        }
    }
});

// select entity
Flight::map( 'select', function( $entity, $args ) {

    if( Flight::empty( 'error' )) {
        $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
        $mapper = new \artabramov\Echidna\Mapper( $repository );
        $mapper->select( $entity, $args );

        if( !empty( $mapper->error )) {
            Flight::set( 'e', $repository->e );
            Flight::set( 'error', $mapper->error );
        }
    }
});

// delete entity
Flight::map( 'delete', function( $entity ) {

    if( Flight::empty( 'error' )) {
        $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
        $mapper = new \artabramov\Echidna\Mapper( $repository );
        $mapper->delete( $entity );

        if( !empty( $mapper->error )) {
            Flight::set( 'e', $repository->e );
            Flight::set( 'error', $mapper->error );
        }
    }
});

// entity exists
Flight::map( 'exists', function( $entity, $args ) {

    $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
    $mapper = new \artabramov\Echidna\Mapper( $repository );
    return $mapper->exists( $entity, $args );
});

// get sequence (entity for sample)
Flight::map( 'sequence', function( $entity, $args, $extras ) {

    if( Flight::empty( 'error' )) {
        $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
        $mapper = new \artabramov\Echidna\Mapper( $repository );
        $sequence = new \artabramov\Echidna\Sequence( $repository, $mapper );
        $result = $sequence->select( $entity, $args, $extras );
    }

    return isset( $result ) ? $result : [];
});

// get repository datetime
Flight::map( 'time', function() {
    $repository = new \artabramov\Echidna\Repository( Flight::get( 'pdo' ) );
    return $repository->time();
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
    Flight::get('pdo')->beginTransaction();
});

// after route
Flight::after('stop', function( &$params, &$output ) {

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
});

// json
Flight::before('json', function( &$params, &$output ) {
    $params[0]['time']    = Flight::time();
    $params[0]['success'] = Flight::empty( 'error' ) ? 'true' : 'false';
    $params[0]['error']   = Flight::empty( 'error' ) ? '' : Flight::get( 'error' );
});

//================ ROUTES ================

// default
Flight::route( 'GET /', function() {
    Flight::render( __DIR__ . '/webapp/index.php', array('page' => 'default', 'title' => 'Echidna'));
});

// welcome
Flight::route( 'GET /hello', function() {
    Flight::render( __DIR__ . '/webapp/index.php', array('page' => 'hello', 'title' => 'Echidna: Hello!'));
});

// documents
Flight::route( 'GET /documents', function() {
    Flight::render( __DIR__ . '/webapp/index.php', array('page' => 'documents', 'title' => 'Echidna: Documents'));
});

// ===================================================================

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
