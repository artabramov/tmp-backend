<?php
namespace App\Core;

class User extends \App\Core\Echidna
{
    protected $error = '';
    protected $id;
    protected $register_date;
    protected $restore_date;
    protected $signin_date;
    protected $auth_date;
    protected $user_status;
    protected $user_token;
    protected $user_email;
    protected $user_hash;

    /*
    protected const PASS_LEN = 6;
    protected const PASS_SYMBOLS = '0123456789abcdefghijklmnopqrstuvwxyz';
    protected const PASS_SALT = '~salt';
    */

    public function __get( string $key ) {

        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return false;
    }

    public function __isset( string $key ) {

        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    // select the user
    public function get( array $args ) : bool {

        foreach( $args as $arg ) {

            if( $arg[0] == 'id' and empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;
            
            } elseif( $arg[0] == 'id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'register_date' and empty( $arg[2] )) {
                $this->error = 'register_date is empty';
                break;

            } elseif( $arg[0] == 'register_date' and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $arg[2] )) {
                $this->error = 'register_date is incorrect';
                break;

            } elseif( $arg[0] == 'restore_date' and empty( $arg[2] )) {
                $this->error = 'restore_date is empty';
                break;

            } elseif( $arg[0] == 'restore_date' and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $arg[2] )) {
                $this->error = 'restore_date is incorrect';
                break;

            } elseif( $arg[0] == 'signin_date' and empty( $arg[2] )) {
                $this->error = 'signin_date is empty';
                break;

            } elseif( $arg[0] == 'signin_date' and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $arg[2] )) {
                $this->error = 'signin_date is incorrect';
                break;

            } elseif( $arg[0] == 'auth_date' and empty( $arg[2] )) {
                $this->error = 'auth_date is empty';
                break;

            } elseif( $arg[0] == 'auth_date' and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $arg[2] )) {
                $this->error = 'auth_date is incorrect';
                break;

            } elseif( $arg[0] == 'user_status' and empty( $arg[2] )) {
                $this->error = 'user_status is empty';
                break;

            } elseif( $arg[0] == 'user_status' and !in_array( $arg[2], ['pending', 'approved', 'trash'] )) {
                $this->error = 'user_status is incorrect';
                break;

            } elseif( $arg[0] == 'user_token' and empty( $arg[2] )) {
                $this->error = 'user_token is empty';
                break;

            } elseif( $arg[0] == 'user_token' and strlen( $arg[2] ) > 80 ) {
                $this->error = 'user_token is incorrect';
                break;

            } elseif( $arg[0] == 'user_email' and empty( $arg[2] )) {
                $this->error = 'user_email is empty';
                break;

            } elseif( $arg[0] == 'user_email' and !preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $arg[2] )) {
                $this->error = 'user_email is incorrect';
                break;

            } elseif( $arg[0] == 'user_hash' and empty( $arg[2] )) {
                $this->error = 'user_hash is empty';
                break;

            } elseif( $arg[0] == 'user_hash' and strlen( $arg[2] ) > 40 ) {
                $this->error = 'user_hash is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'users', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'user not found';
        
            } else {
                $this->id            = $rows[0]->id;
                $this->register_date = $rows[0]->register_date;
                $this->restore_date  = $rows[0]->restore_date;
                $this->signin_date   = $rows[0]->signin_date;
                $this->auth_date     = $rows[0]->auth_date;
                $this->user_status   = $rows[0]->user_status;
                $this->user_token    = $rows[0]->user_token;
                $this->user_email    = $rows[0]->user_email;
                $this->user_hash     = $rows[0]->user_hash;
            }
        }

        return empty( $this->error );
    }

    // create a new user
    public function set( array $data ) : bool {

        if( empty( $data['register_date'] )) {
            $this->error = 'register_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['register_date'] )) {
            $this->error = 'register_date is incorrect';

        } elseif( empty( $data['restore_date'] )) {
            $this->error = 'restore_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['restore_date'] )) {
            $this->error = 'restore_date is incorrect';

        } elseif( empty( $data['signin_date'] )) {
            $this->error = 'signin_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['signin_date'] )) {
            $this->error = 'signin_date is incorrect';

        } elseif( empty( $data['auth_date'] )) {
            $this->error = 'auth_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['auth_date'] )) {
            $this->error = 'auth_date is incorrect';

        } elseif( empty( $data['user_status'] )) {
            $this->error = 'user_status is empty';

        } elseif( !in_array( $data['user_status'], ['pending', 'approved', 'trash'] )) {
            $this->error = 'user_status is incorrect';

        } elseif( empty( $data['user_token'] )) {
            $this->error = 'user_token is empty';

        } elseif( strlen( $data['user_token'] ) > 80 ) {
            $this->error = 'user_token is incorrect';

        } elseif( $this->is_exists( 'users', [['user_token','=', $data['user_token']]] )) {
            $this->error = 'user_token is occupied';

        } elseif( empty( $data['user_email'] )) {
            $this->error = 'user_email is empty';

        } elseif( !preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $data['user_email'] )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( 'users', [['user_email','=', $data['user_email']]] )) {
            $this->error = 'user_email is occupied';

        } elseif( !is_string( $data['user_hash'] ) or strlen( $data['user_hash'] ) > 40 ) {
            $this->error = 'user_hash is incorrect';

        } else {
            $this->id = $this->insert( 'users', $data );

            if( empty( $this->id )) {
                $this->error = 'user insert error';

            } else {
                $this->register_date = $data['register_date'];
                $this->restore_date  = $data['restore_date'];
                $this->signin_date   = $data['signin_date'];
                $this->auth_date     = $data['auth_date'];
                $this->user_status   = $data['user_status'];
                $this->user_token    = $data['user_token'];
                $this->user_email    = $data['user_email'];
                $this->user_hash     = $data['user_hash'];
            }
        }

        return empty( $this->error );
    }

    // update user by $this->id
    public function put( array $data ) : bool {

        if( empty( $this->id )) {
            $this->error = 'user_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( array_key_exists('register_date', $data) and empty( $data['register_date'] )) {
            $this->error = 'register_date is empty';

        } elseif( array_key_exists('register_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['register_date'] )) {
            $this->error = 'register_date is incorrect';

        } elseif( array_key_exists('restore_date', $data) and empty( $data['restore_date'] )) {
            $this->error = 'restore_date is empty';

        } elseif( array_key_exists('restore_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['restore_date'] )) {
            $this->error = 'restore_date is incorrect';

        } elseif( array_key_exists('signin_date', $data) and empty( $data['signin_date'] )) {
            $this->error = 'signin_date is empty';

        } elseif( array_key_exists('signin_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['signin_date'] )) {
            $this->error = 'signin_date is incorrect';

        } elseif( array_key_exists('auth_date', $data) and empty( $data['auth_date'] )) {
            $this->error = 'auth_date is empty';

        } elseif( array_key_exists('auth_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['auth_date'] )) {
            $this->error = 'auth_date is incorrect';

        } elseif( array_key_exists('user_status', $data) and empty( $data['user_status'] )) {
            $this->error = 'user_status is empty';

        } elseif( array_key_exists('user_status', $data) and !in_array( $data['user_status'], ['pending', 'approved', 'trash'] )) {
            $this->error = 'user_status is incorrect';

        } elseif( array_key_exists('user_token', $data) and empty( $data['user_token'] )) {
            $this->error = 'user_token is empty';

        } elseif( array_key_exists('user_token', $data) and strlen( $data['user_token'] ) > 80 ) {
            $this->error = 'user_token is incorrect';

        } elseif( array_key_exists('user_token', $data) and $this->is_exists( 'users', [['user_token','=', $data['user_token']]] )) {
            $this->error = 'user_token is occupied';

        } elseif( array_key_exists('user_email', $data) and empty( $data['user_email'] )) {
            $this->error = 'user_email is empty';

        } elseif( array_key_exists('user_email', $data) and !preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $data['user_email'] )) {
            $this->error = 'user_email is incorrect';

        } elseif( array_key_exists('user_email', $data) and $this->is_exists( 'users', [['user_email','=', $data['user_email']]] )) {
            $this->error = 'user_email is occupied';

        } elseif( array_key_exists('user_hash', $data) and strlen( $data['user_hash'] ) > 40 ) {
            $this->error = 'user_hash is incorrect';

        } elseif( !$this->update( 'users', [['id', '=', $this->id]], $data )) {
            $this->error = 'user update error';

        } else {
            foreach( $data as $key => $value ) {
                if( !in_array( $key, ['pdo', 'e', 'error'] ) and property_exists( $this, $key )) {
                    $this->$key = $value;
                }
            }
        }

        return empty( $this->error );
    }

    // delete user by $this->id
    public function del() : bool {

        if( empty( $this->id )) {
            $this->error = 'user_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->delete( 'users', [['id', '=', $this->id]] )) {
            $this->error = 'user delete error';

        } else {
            $this->id            = null;
            $this->register_date = null;
            $this->restore_date  = null;
            $this->signin_date   = null;
            $this->auth_date     = null;
            $this->user_status   = null;
            $this->user_token    = null;
            $this->user_email    = null;
            $this->user_hash     = null;
        }

        return empty( $this->error );
    }

}
