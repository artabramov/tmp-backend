<?php
namespace App\Core;

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
