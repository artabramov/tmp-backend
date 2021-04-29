<?php
namespace App\Core;

class Collector extends \App\Core\Echidna
{
    public $rows = [];

    public function get( $model, $table, $args, $limit, $offset ) {
        $rows = $this->select( 'id', $table, $args, $limit, $offset );

        foreach( $rows as $row ) {
            $instance = new $model( $this->pdo );
            $instance->get( intval( $row->id ));
            array_push( $this->rows, $instance );
        }

        return !empty( $rows );
    }

    public function pull() : array {
        $output = [];
        foreach( $this->rows as $row ) {
            array_push( $output, $row->pull() );
        }
        return $output;
    }
}
