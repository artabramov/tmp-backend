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
    $pass_len = 10;

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

// datetime
Flight::map( 'datetime', function() {
    $time = new \App\Core\Time( Flight::get( 'pdo' ));
    return $time->datetime;
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
Flight::map( 'dropbox_upload', function( $file, $upload_file ) {

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

// exist
Flight::map( 'exist', function() {
    return new \App\Core\Exist( Flight::get( 'pdo' ));
});

// create the user
Flight::map( 'user', function( $data = [] ) {

    $user = new \App\Core\Row( Flight::get( 'pdo' ), 
        'users', [
        'id'           => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date'  => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date'  => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'restore_date' => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'user_status'  => [ "/^pending$|^approved$|^trash$/", false ],
        'user_token'   => [ "/^[0-9a-f]{80}$/", true ],
        'user_email'   => [ "/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", true ],
        'user_name'    => [ "/^.{1,128}$/", false ],
        'user_hash'    => [ "/^$|^[0-9a-f]{40}$/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $user->$key = $value;
    }

    return $user;
});

// usermeta
Flight::map( 'usermeta', function( $data = [] ) {

    $meta = new \App\Core\Row( Flight::get( 'pdo' ), 
        'user_meta', [
        'id'          => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'user_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'meta_key'    => [ "/^[a-z0-9_]{1,20}$/", false ],
        'meta_value'  => [ "/^.{0,255}$/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $meta->$key = $value;
    }

    return $meta;
});

// repo
Flight::map( 'repo', function( $data = [] ) {

    $repo = new \App\Core\Row( Flight::get( 'pdo' ), 
        'repos', [
        'id'          => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'user_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'repo_status' => [ "/^private$|^custom$|^trash$/", false ],
        'repo_name'   => [ "/^.{1,128}$/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $repo->$key = $value;
    }

    return $repo;
});

// role
Flight::map( 'role', function( $data = [] ) {

    $role = new \App\Core\Row( Flight::get( 'pdo' ), 
        'user_roles', [
        'id'          => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'repo_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'user_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'user_role'   => [ "/^admin$|^author$|^editor$|^reader$|^none$/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $role->$key = $value;
    }

    return $role;
});

// post
Flight::map( 'post', function( $data = [] ) {

    $post = new \App\Core\Row( Flight::get( 'pdo' ), 
        'posts', [
        'id'           => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date'  => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date'  => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'parent_id'    => [ "/^$|^[1-9][0-9]{0,20}$/", false ],
        'user_id'    => [ "/^[1-9][0-9]{0,20}$/", false ],
        'repo_id'    => [ "/^[1-9][0-9]{0,20}$/", false ],
        'post_status'  => [ "/^todo$|^doing$|^done$|^comment$|^trash$/", false ],
        'post_content' => [ "/^.{1,4048}?/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $user->$key = $value;
    }

    return $post;
});

// postmeta
Flight::map( 'postmeta', function( $data = [] ) {

    $meta = new \App\Core\Row( Flight::get( 'pdo' ), 
        'post_meta', [
        'id'          => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date' => [ "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'post_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'meta_key'    => [ "/^[a-z0-9_]{1,20}$/", false ],
        'meta_value'  => [ "/^.{1,255}$/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $meta->$key = $value;
    }

    return $meta;
});

//upload
Flight::map( 'upload', function( $data = [] ) {

    $upload = new \App\Core\Row( Flight::get( 'pdo' ), 
        'post_uploads', [
        'id'          => [ "/^[1-9][0-9]{0,20}$/", false ],
        'create_date' => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'update_date' => [ "/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", false ],
        'user_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'repo_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'post_id'     => [ "/^[1-9][0-9]{0,20}$/", false ],
        'upload_key'  => [ "/^[a-z0-9_]{1,20}$/", false ],
        'upload_name' => [ "/^.{1,255}$/", false ],
        'upload_mime' => [ "/^.{1,255}$/", false ],
        'upload_size' => [ "/^[1-9][0-9]{0,20}$/", false ],
        'upload_file' => [ "/^.{1,255}$/", false ],
    ]);

    foreach( $data as $key=>$value ) {
        $user->$key = $value;
    }

    return $upload;
});

//===========================================================

// insert
Flight::map( 'insert', function( $row, $data ) {

    if( Flight::empty( 'error' )) {
        if( !$row->set( $data )) {
            Flight::set( 'e', $row->e );
            Flight::set( 'error', $row->error );
        }
    }
});

// update
Flight::map( 'update', function( $row, $data ) {

    if( Flight::empty( 'error' )) {
        if( !$row->put( $data )) {
            Flight::set( 'e', $row->e );
            Flight::set( 'error', $row->error );
        }
    }
});

// select
Flight::map( 'select', function( $row, $args ) {

    if( Flight::empty( 'error' )) {
        if( !$row->get( $args )) {
            Flight::set( 'e', $row->e );
            Flight::set( 'error', $row->error );
        }
    }
});

// delete
Flight::map( 'delete', function( $row ) {

    if( Flight::empty( 'error' )) {
        if( !$row->del()) {
            Flight::set( 'e', $row->e );
            Flight::set( 'error', $row->error );
        }
    }
});

// auth
Flight::map( 'auth', function( $user_token ) {

    $user = Flight::user();
    Flight::select( $user, [
        ['user_token', '=', $user_token], 
        ['user_status', '=', 'approved']   
    ]);

    $now = Flight::datetime();
    if( Flight::empty( 'error' ) and strtotime( $now ) - strtotime( $user->restore_date ) > 60 * 60 * 24 * 1 ) {
        Flight::set( 'error', 'user_token is expired' );
    }

    return $user;
});

// ==== FILTERING ====

// before route
Flight::before('start', function(&$params, &$output){
    Flight::get('pdo')->beginTransaction();
});

// after route
Flight::after('stop', function(&$params, &$output){

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
Flight::before('json', function(&$params, &$output){
    $params[0]['time']    = Flight::datetime();
    $params[0]['success'] = Flight::empty( 'error' ) ? 'true' : 'false';
    $params[0]['error']   = Flight::empty( 'error' ) ? '' : Flight::get( 'error' );
});

//================ ROUTES ================


Flight::route( 'GET /', function() {

    class Repository
    {
        protected $exception;
        protected $pdo;

        public function __construct( $pdo ) {
            $this->exception = null;
            $this->pdo = $pdo;
        }

        public function insert( string $table, array $data ) : int {

            $keys = '';
            $values = '';
            foreach( $data as $key=>$value ) {
                $keys .= empty( $keys ) ? $key : ', ' . $key;
                $values .= empty( $values ) ? ':' . $key : ', ' . ':' . $key;
            }
    
            try {
                $stmt = $this->pdo->prepare( 'INSERT INTO ' . $table . ' ( ' . $keys . ' ) VALUES ( ' . $values . ' )' );
                foreach( $data as $key=>$value ) {
                    $stmt->bindParam( ':' . $key, $data[ $key ] );
                }
    
                $stmt->execute();
                $id = $this->pdo->lastInsertId();
    
            } catch( \Exception $e ) {
                $this->exception = $e;
            }
    
            return empty( $this->exception ) ? $id : 0;
        }

        public function is_exists( string $table, array $args ) : bool {

            $where = '';
            foreach( $args as $arg ) {
                $where .= empty( $where ) ? 'WHERE ' : ' AND ';
                $where .= $arg[0] . $arg[1] . ':' . $arg[0];
            }
    
            try {
                $stmt = $this->pdo->prepare( 'SELECT id FROM ' . $table . ' ' . $where . ' LIMIT 1' );
                foreach( $args as $arg ) {
                    $stmt->bindParam( ':' . $arg[0], $arg[2]  );
                }
    
                $stmt->execute();
                $rows = $stmt->fetch( $this->pdo::FETCH_OBJ );
    
            } catch( \Exception $e ) {
                $this->e = $e;
            }
    
            return empty( $this->e ) ? !empty( $rows->id ) : false;
        }
    }


    class Mapper
    {
        protected $error;
        protected $repository;

        public function __construct( $repository ) {
            $this->error = '';
            $this->repository = $repository;
        }

        public function __isset( $key ) {
            if( property_exists( $this, $key )) {
                return !empty( $this->$key );
            }
            return false;
        }

        public function __get( $key ) {
            if( property_exists( $this, $key )) {
                return $this->$key;
            }
        }

        // doc format: @key(param1=value1 param2=value2)
        private function parse_params( $doc, $key ) {

            preg_match_all( '#@' . $key . '\((.*?)\)\n#s', $doc, $tmp );
            preg_match_all( '/\s*([^=]+)=(\S+)\s*/', !empty($tmp[1][0]) ? $tmp[1][0] : '', $tmp );
            return array_combine ( $tmp[1], $tmp[2] );
        }

        private function get_entity_params( $entity ) {

            $class = new ReflectionClass( $entity );
            $doc = $class->getDocComment();
            return $this->parse_params( $doc, 'entity' );
        }

        private function get_column_params( $entity, $column ) {

            $class = new ReflectionClass( $entity );
            $property = $class->getProperty( $column );
            $doc = $property->getDocComment();
            return $this->parse_params( $doc, 'column' );
        }

        public function save( $entity ) {

            $this->error = '';
            $entity_params = $this->get_entity_params( $entity );
            $class = new ReflectionClass( $entity );
            $properties = $class->getProperties();
            $data = [];

            foreach( $properties as $property ) {
                $property_name = $property->name;
                $property = $class->getProperty( $property_name );
                $property->setAccessible( true );
                $property_value = $property->getValue( $entity );
                $property_params = $this->get_column_params( $entity, $property_name );

                $property_name = array_key_exists( 'name', $property_params ) ? strval( $property_params['name'] ) : '';
                $property_type = array_key_exists( 'type', $property_params ) ? strval( $property_params['type'] ) : '';
                $property_length = array_key_exists( 'length', $property_params ) ? intval( $property_params['length'] ) : 0;
                $property_unique = array_key_exists( 'unique', $property_params ) ? boolval( $property_params['unique'] ) : false;
                $property_nullable = array_key_exists( 'nullable', $property_params ) ? boolval( $property_params['nullable'] ) : false;
                $property_regex = array_key_exists( 'regex', $property_params ) ? strval( $property_params['regex'] ) : '/^.*$/';

                if( !$property_nullable and empty( $property_value )) {
                    $this->error = $property_name . ' is empty';
                    break;

                } elseif( !empty( $property_value ) and $property_type != gettype( $property_value )) {
                    $this->error = $property_name . ' has incorrect type';
                    break;

                } elseif( !empty( $property_length ) and mb_strlen( $property_value ) > $property_length ) {
                    $this->error = $property_name . ' is too long';
                    break;

                } elseif( !empty( $property_regex ) and !preg_match( $property_regex, $property_value ) ) {
                    $this->error = $property_name . ' does not match the pattern';
                    break;

                } elseif( $property_unique and $this->repository->is_exists( $entity_params['table'], [['user_email', '=', $property_value]] ) ) {
                    $this->error = $property_name . ' is occupied';
                    break;

                } elseif( !empty( $property_value )) {
                    $data[$property_name] = $property_value;
                }
            }



            if( empty( $this->error )) {
                if( empty( $this->id )) {
                    $this->repository->insert( $entity_params['table'], $data );

                    

                    $a = 1;

                } else {
                    //$this->repositoty->update( $entity );
                }
            }



        }
    }

    class Entity
    {
        public function __set( $key, $value ) {
            if( property_exists( $this, $key )) {
                $this->$key = $value;
            }            
        }

        public function __get( $key ) {
            if( property_exists( $this, $key )) {
                return $this->$key;
            }            
        }
    }

    /**
     * @entity(name=user table=users)
     */
    class User extends Entity
    {
        /**
         * @column(name=id type=integer unique=true nullable=true)
         */
        protected $id;

        /**
         * @column(name=user_email type=string length=255 nullable=false unique=true regex=/^\S+@\S+\.\S+$/)
         */
        protected $user_email;

        /**
         * @column(name=user_name type=string length=10)
         */
        protected $user_name;

        /**
         * @column(name=user_token nullable=false type=string regex=/^[0-9a-f]{80}$/)
         */
        protected $user_token;
    }

    //--------------------------------------------------------------

    $user_email = (string) Flight::request()->query['user_email'];
    $user_name = (string) Flight::request()->query['user_name'];


    $repository = new Repository( Flight::get( 'pdo' ) );

    $user = new User;
    //$user->id = 11;
    $user->user_email = $user_email;
    $user->user_name = $user_name;
    $user->user_token = '65656a668d4634d6f1abc6d5ff182b9fdbc92561960b64a66b43e75128973f6e418de8a3219923a1';
    //$user->register_date = '2000-01-01 00:00:00';

    $mapper = new Mapper( $repository );
    $mapper->save( $user );

    if( !empty( $mapper->error )) {
        echo $mapper->error;

    } else {
        echo 'hurrah!';
    }
    


    //--------------------------------------------------------












    /*
    $user = Flight::user([
        'user_status' => 'pending',
        'user_token'  => Flight::token(),
        'user_email'  => Flight::request()->query['user_email'],
        'user_name'   => Flight::request()->query['user_name'],
    ]);
    Flight::save( $user );

    Flight::json([ 'error' => $user->error ]);
    */

    /*
    $pdo = require __DIR__ . "/../src/init/pdo.php";
    $attribute = new \App\Core\Attribute( $pdo );
    $attribute->set(['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 1, 'attribute_key' => 'key', 'attribute_value' => 'value']);
    
    */
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

// user rename
Flight::route( 'PUT /user', function() {
    require_once( '../src/routes/user_update.php' );
});

// repo create
Flight::route( 'POST /repo', function() {
    require_once( '../src/routes/repo_insert.php' );
});

// repo update
Flight::route( 'PUT /repo/@repo_id', function( $repo_id ) {
    require_once( '../src/routes/repo_update.php' );
});

// repo delete
Flight::route( 'DELETE /repo/@repo_id', function( $repo_id ) {
    require_once( '../src/routes/repo_delete.php' );
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

// create document
Flight::route( 'POST /post', function() {
    require_once( '../src/routes/post_insert.php' );
});

// select the document
Flight::route( 'GET /post/@post_id', function( $post_id ) {
    require_once( '../src/routes/post_select.php' );
});

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
