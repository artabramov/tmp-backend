<?php
namespace App\Core;

class Finder extends \App\Core\Echidna
{
    public $rows = [];

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

    public function find( $model, $table, $args, $limit, $offset ) {
        $rows = $this->select( 'id', $table, $args, $limit, $offset );

        foreach( $rows as $row ) {
            $instance = new $model( $this->pdo );
            $instance->get( [['id', '=', $row->id]] );
            array_push( $this->rows, $instance );
        }

        return !empty( $rows );
    }
}
