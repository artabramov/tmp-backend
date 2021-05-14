<?php
namespace App\Core;

class Time extends \App\Core\Echidna
{

    public function __get( string $key ) {

        if( $key == 'datetime' ) {
            $rows = $this->query("SELECT NOW() AS datetime;");

            if( !empty( $rows[0] )) {
                return $rows[0]->datetime;
            }
        }
        
        return null;
    }

}
