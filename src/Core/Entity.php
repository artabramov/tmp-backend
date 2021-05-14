<?php
namespace App\Core;

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
