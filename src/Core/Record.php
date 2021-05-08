<?php
namespace App\Core;

class Record extends \App\Core\Echidna
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

        if( $key == 'error' ) {
            $this->error = $value;

        } elseif( array_key_exists( $key, $this->keys )) {
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

    public function set( $data ) : bool {

        $this->e = null;
        $this->error = '';

        foreach( $data as $key => $value ) {

            if( !preg_match( $this->keys[ $key ][0], $value ) ) {
                $this->error = $key . ' is incorrect';

            } elseif( $this->keys[ $key ][1] and $this->is_exists( $this->table, [[$key, '=', $value]] ) ) {
                $this->error = $key . ' is occupied';
            }
        }

        if( empty( $this->error )) {

            $id = $this->insert( $this->table, $data );

            if( $id > 0 ) {
                $this->data['id'] = $id;
                foreach( $data as $key => $value ) {
                    $this->$key = $value;
                }

            } else {
                $this->error = 'insert error';
            }
        }

        return empty( $this->error );
    }

    public function put( $data ) : bool {

        $this->e = null;
        $this->error = '';

        foreach( $data as $key => $value ) {

            if( !preg_match( $this->keys[ $key ][0], $value ) ) {
                $this->error = $key . ' is incorrect';

            } elseif( $this->keys[ $key ][1] and $this->is_exists( $this->table, [[$key, '=', $value]] ) ) {
                $this->error = $key . ' is occupied';
            }
        }

        if( empty( $this->error )) {
    
            if( $this->update( $this->table, [['id', '=', $this->data['id']]], $data )) {
                foreach( $data as $key => $value ) {
                    $this->$key = $value;
                }

            } else {
                $this->error = 'update error';
            }
        }

        return empty( $this->error );
    }

    public function get( $args ) {

        $rows = $this->select( '*', $this->table, $args, 1, 0 );

        if ( empty( $rows[0] )) {
            $this->error = 'not found';
    
        } else {
            $data = get_object_vars( $rows[0] );

            foreach( $data as $key => $value ) {
                $this->$key = $value;
            }
        }

        return empty( $this->error );
    }

    public function del() {

        $this->e = null;
        $this->error = '';

        if( $this->delete( $this->table, [['id', '=', $this->data['id']]] )) {
            foreach( $data as $key => $value ) {
                $this->$key = null;
            }

        } else {
            $this->error = 'delete error';
        }

        return empty( $this->error );
    }








    public function save( $data = [] ) {

        $this->e = null;
        $this->error = '';

        foreach( $data as $key => $value ) {
            $this->$key = $value;
        }

        foreach( $this->data as $key => $value ) {

            if( !preg_match( $this->keys[ $key ][0], $value ) ) {
                $this->error = $key . ' is incorrect';

            } elseif( empty( $this->data['id'] ) and $this->keys[ $key ][1] and $this->is_exists( $this->table, [[$key, '=', $value]] ) ) {
                $this->error = $key . ' is occupied';
            }
        }

        if( empty( $this->error )) {

            // insert
            if( empty( $this->data['id'] ) ) {
                $id = $this->insert( $this->table, $this->data );
                if( $id > 0 ) {
                    $this->data['id'] = $id;
                } else {
                    $this->error = 'insert error';
                }
    
            // update
            } elseif( !$this->update( $this->table, [['id', '=', $this->data['id']]], $this->data )) {
                $this->error = 'update error';
            }
        }

        return empty( $this->error );
    }





}
