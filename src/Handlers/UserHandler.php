<?php
namespace App\Handlers;

class UserHandler
{
    protected $error;
    protected $em;
    protected $user;

    public function __construct($em, \App\Entities\User $user) {
        $this->error = '';
        $this->em = $em;
        $this->user = $user;
    }

    public function __set( $key, $value ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return null;
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }


    // User register
    public function insert() {
        if(empty($this->user_name)) {
            $this->error = 'error!';
        }
    }

    public function update() {}

    public function delete() {}

    public function select($args) {}

}