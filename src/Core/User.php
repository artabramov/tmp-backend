<?php
namespace App\Core;

class User extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $register_date;
    protected $restore_date;
    protected $signin_date;
    protected $auth_date;
    protected $user_status;
    protected $user_token;
    protected $user_email;
    protected $user_name;
    protected $user_hash;

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

    public function get( array $args ) : bool {

        foreach( $args as $arg ) {

            if( $arg[0] == 'id' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'register_date' and $this->is_empty( $arg[2] )) {
                $this->error = 'register_date is empty';
                break;

            } elseif( $arg[0] == 'register_date' and !$this->is_datetime( $arg[2] )) {
                $this->error = 'register_date is incorrect';
                break;

            } elseif( $arg[0] == 'restore_date' and $this->is_empty( $arg[2] )) {
                $this->error = 'restore_date is empty';
                break;

            } elseif( $arg[0] == 'restore_date' and !$this->is_datetime( $arg[2] )) {
                $this->error = 'restore_date is incorrect';
                break;

            } elseif( $arg[0] == 'signin_date' and $this->is_empty( $arg[2] )) {
                $this->error = 'signin_date is empty';
                break;

            } elseif( $arg[0] == 'signin_date' and !$this->is_datetime( $arg[2] )) {
                $this->error = 'signin_date is incorrect';
                break;

            } elseif( $arg[0] == 'auth_date' and $this->is_empty( $arg[2] )) {
                $this->error = 'auth_date is empty';
                break;

            } elseif( $arg[0] == 'auth_date' and !$this->is_datetime( $arg[2] )) {
                $this->error = 'auth_date is incorrect';
                break;

            } elseif( $arg[0] == 'user_status' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_status is empty';
                break;

            } elseif( $arg[0] == 'user_status' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'user_status is incorrect';
                break;

            } elseif( $arg[0] == 'user_token' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_token is empty';
                break;

            } elseif( $arg[0] == 'user_token' and !$this->is_string( $arg[2], 80 )) {
                $this->error = 'user_token is incorrect';
                break;

            } elseif( $arg[0] == 'user_email' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_email is empty';
                break;

            } elseif( $arg[0] == 'user_email' and !$this->is_email( $arg[2] )) {
                $this->error = 'user_email is incorrect';
                break;

            } elseif( $arg[0] == 'user_name' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_name is empty';
                break;

            } elseif( $arg[0] == 'user_name' and !$this->is_string( $arg[2], 128 )) {
                $this->error = 'user_name is incorrect';
                break;

            } elseif( $arg[0] == 'user_hash' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_hash is empty';
                break;

            } elseif( $arg[0] == 'user_hash' and !$this->is_string( $arg[2], 40 )) {
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
                $this->user_name     = $rows[0]->user_name;
                $this->user_hash     = $rows[0]->user_hash;
            }
        }

        return empty( $this->error );
    }

    public function set( array $data ) : bool {

        if( !array_key_exists('register_date', $data) or $this->is_empty( $data['register_date'] )) {
            $this->error = 'register_date is empty';

        } elseif( !$this->is_datetime( $data['register_date'] )) {
            $this->error = 'register_date is incorrect';

        } elseif( !array_key_exists('restore_date', $data) or $this->is_empty( $data['restore_date'] )) {
            $this->error = 'restore_date is empty';

        } elseif( !$this->is_datetime( $data['restore_date'] )) {
            $this->error = 'restore_date is incorrect';

        } elseif( !array_key_exists('signin_date', $data) or $this->is_empty( $data['signin_date'] )) {
            $this->error = 'signin_date is empty';

        } elseif( !$this->is_datetime( $data['signin_date'] )) {
            $this->error = 'signin_date is incorrect';

        } elseif( !array_key_exists('auth_date', $data) or $this->is_empty( $data['auth_date'] )) {
            $this->error = 'auth_date is empty';

        } elseif( !$this->is_datetime( $data['auth_date'] )) {
            $this->error = 'auth_date is incorrect';

        } elseif( !array_key_exists('user_status', $data) or $this->is_empty( $data['user_status'] )) {
            $this->error = 'user_status is empty';

        } elseif( !$this->is_string( $data['user_status'], 20 )) {
            $this->error = 'user_status is incorrect';

        } elseif( !array_key_exists('user_token', $data) or $this->is_empty( $data['user_token'] )) {
            $this->error = 'user_token is empty';

        } elseif( !$this->is_string( $data['user_token'], 80 )) {
            $this->error = 'user_token is incorrect';

        } elseif( $this->is_exists( 'users', [['user_token','=', $data['user_token']]] )) {
            $this->error = 'user_token is occupied';

        } elseif( !array_key_exists('user_email', $data) or $this->is_empty( $data['user_email'] )) {
            $this->error = 'user_email is empty';

        } elseif( !$this->is_email( $data['user_email'] )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( 'users', [['user_email','=', $data['user_email']]] )) {
            $this->error = 'user_email is occupied';

        } elseif( !array_key_exists('user_name', $data) or $this->is_empty( $data['user_name'] )) {
            $this->error = 'user_name is empty';

        } elseif( !$this->is_string( $data['user_status'], 20 )) {
            $this->error = 'user_status is incorrect';

        } elseif( !$this->is_string( $data['user_hash'], 40 )) {
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
                $this->user_name     = $data['user_name'];
                $this->user_hash     = $data['user_hash'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'hub_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( array_key_exists('register_date', $data) and $this->is_empty( $data['register_date'] )) {
            $this->error = 'register_date is empty';

        } elseif( array_key_exists('register_date', $data) and !$this->is_datetime( $data['register_date'] )) {
            $this->error = 'register_date is incorrect';

        } elseif( array_key_exists('restore_date', $data) and $this->is_empty( $data['restore_date'] )) {
            $this->error = 'restore_date is empty';

        } elseif( array_key_exists('restore_date', $data) and !$this->is_datetime( $data['restore_date'] )) {
            $this->error = 'restore_date is incorrect';

        } elseif( array_key_exists('signin_date', $data) and $this->is_empty( $data['signin_date'] )) {
            $this->error = 'signin_date is empty';

        } elseif( array_key_exists('signin_date', $data) and !$this->is_datetime( $data['signin_date'] )) {
            $this->error = 'signin_date is incorrect';

        } elseif( array_key_exists('auth_date', $data) and $this->is_empty( $data['auth_date'] )) {
            $this->error = 'auth_date is empty';

        } elseif( array_key_exists('auth_date', $data) and !$this->is_datetime( $data['auth_date'] )) {
            $this->error = 'auth_date is incorrect';

        } elseif( array_key_exists('user_status', $data) and $this->is_empty( $data['user_status'] )) {
            $this->error = 'user_status is empty';

        } elseif( array_key_exists('user_status', $data) and !$this->is_string( $data['user_status'], 20 )) {
            $this->error = 'user_status is incorrect';

        } elseif( array_key_exists('user_token', $data) and $this->is_empty( $data['user_token'] )) {
            $this->error = 'user_token is empty';

        } elseif( array_key_exists('user_token', $data) and !$this->is_string( $data['user_token'], 80 )) {
            $this->error = 'user_token is incorrect';

        } elseif( array_key_exists('user_token', $data) and $this->is_exists( 'users', [['user_token','=', $data['user_token']]] )) {
            $this->error = 'user_token is occupied';

        } elseif( array_key_exists('user_email', $data) and $this->is_empty( $data['user_email'] )) {
            $this->error = 'user_email is empty';

        } elseif( array_key_exists('user_email', $data) and !$this->is_email( $data['user_email'] )) {
            $this->error = 'user_email is incorrect';

        } elseif( array_key_exists('user_email', $data) and $this->is_exists( 'users', [['user_email','=', $data['user_email']]] )) {
            $this->error = 'user_email is occupied';

        } elseif( array_key_exists('user_name', $data) and $this->is_empty( $data['user_name'] )) {
            $this->error = 'user_name is empty';

        } elseif( array_key_exists('user_name', $data) and !$this->is_string( $data['user_name'], 128 )) {
            $this->error = 'user_name is incorrect';

        } elseif( array_key_exists('user_hash', $data) and !$this->is_string( $data['user_hash'], 40 )) {
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

    public function del() : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_num( $this->id )) {
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
            $this->user_name     = null;
            $this->user_hash     = null;
        }

        return empty( $this->error );
    }

}
