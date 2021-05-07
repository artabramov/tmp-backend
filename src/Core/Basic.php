<?php
namespace App\Core;

class Basic extends \App\Core\Echidna
{
    protected $error;
    protected $table;
    protected $keys;
    protected $data;

    public function __construct( \PDO $pdo, string $table, array $keys ) {

        parent::__construct( $pdo );
        $this->error = '';
        $this->table = $table;
        $this->keys = $keys;
        $this->data = [];
    }

    public function __get( string $key ) {

        if( $key == 'error' ) {
            return $this->error;

        } elseif( array_key_exists( $key, $this->data )) {
            return $this->data[ $key ];
        }
        return null;
    }

    public function __set( string $key, $value ) {

        if( array_key_exists( $key, $this->keys )) {
            $this->data[ $key ] = $value;
        }
    }

    public function __isset( string $key ) {

        if( $key == 'error' ) {
            return !empty( $this->error );

        } elseif( array_key_exists( $key, $this->data )) {
            return !empty( $this->$data[ $key ] );
        }
        return false;
    }

    /*
    protected function is_empty( mixed $value ) : bool {
        return ( is_string( $value ) and empty( trim( $value ))) or empty( $value );
    }

    protected function is_num( mixed $value ) : bool {
        return ( is_string( $value ) and ctype_digit( $value )) or ( is_int( $value ) and $value >= 0 );
    }

    protected function is_string( mixed $value, int $len ) : bool {
        if( $len > 0 ) {
            return is_string( $value ) and mb_strlen( $value, 'UTF-8' ) <= $len;

        } else {
            return is_string( $value );
        }
    }

    protected function is_datetime( mixed $value ) : bool {
        return is_string( $value ) and preg_match( "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $value );
    }

    protected function is_email( mixed $value ) : bool {
        return is_string( $value ) and preg_match( "/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value );
    }
    */

    public function save( $data = [] ) {

        $this->e = null;
        $this->error = '';

        foreach( $data as $key => $value ) {
            $this->$key = $value;
        }

        foreach( $this->data as $key => $value ) {

            if( !preg_match( $this->keys[ $key ][0], $value ) ) {
                $this->error = $key . ' is incorrect';

            } elseif( $this->keys[ $key ][1] and $this->is_exists( $this->table, [[$key, '=', $value]] ) ) {
                $this->error = $key . ' is occupied';
            }
        }

        if( empty( $this->error )) {

            if( empty( $this->data['id'] ) ) {
                if( !$this->insert( $this->table, $this->data )) {
                    $this->error = 'insert error';
                }
    
            } else {
                if( !$this->update( $this->table, [['id', '=', $this->data['id']]], $this->data )) {
                    $this->error = 'update error';
                }
            }
        }

        return empty( $this->error );
    }


    public function load( $args ) {

        $rows = $this->select( '*', $this->table, $args, 1, 0 );

        if ( empty( $rows[0] )) {
            $this->error = 'not found';
    
        } else {
            $data = get_object_vars( $rows[0] );

            foreach( $data as $key => $value ) {
                $this->$key = $value;
            }
        }
    }


}
