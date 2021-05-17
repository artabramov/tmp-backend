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

    /**
     * @return integer
     * @throws \Exception
     */
    public function insert( string $table, array $data ) : int {

        $columns = implode( ', ', array_keys( $data ));
        $values = implode( ', ', array_fill( 0, count( $data ), '?' ));

        try {
            $stmt = $this->pdo->prepare( 'INSERT INTO ' . $table . ' ( ' . $columns . ' ) VALUES ( ' . $values . ' )' );
            $stmt->execute( array_values( $data ));
            $id = $this->pdo->lastInsertId();

        } catch( \Exception $e ) {
            $this->exception = $e;
        }

        return empty( $this->exception ) ? $id : 0;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function update( string $table, array $args, array $data ) : bool {

        $set = implode( ', ', array_map( fn( $value ) => $value . ' = ?', array_keys( $data )));
        $where = implode( ' AND ', array_map( fn( $value ) => !is_array( $value[2] ) ? $value[0] . ' ' . $value[1] . ' ?' : $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')', $args ));

        $params = array_values( $data );
        foreach( $args as $arg ) {
            if( is_array( $arg[2] )) {
                foreach( $arg[2] as $param ) {
                    $params[] = $param;
                }
            } else {
                $params[] = $arg[2];
            }
        }

        try {
            $stmt = $this->pdo->prepare( 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $where . ' LIMIT 1' );
            $stmt->execute( $params );
            $rows = $stmt->rowCount();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function select( array $columns, string $table, array $args, int $limit, int $offset ) : array {

        $select = implode( ', ', $columns );
        $where = implode( ' AND ', array_map( fn( $value ) => !is_array( $value[2] ) ? $value[0] . ' ' . $value[1] . ' ?' : $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')', $args ));
    
        $params = [];
        foreach( $args as $arg ) {
            if( is_array( $arg[2] )) {
                foreach( $arg[2] as $param ) {
                    $params[] = $param;
                }
            } else {
                $params[] = $arg[2];
            }
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT ' . $select . ' FROM ' . $table . ' WHERE ' . $where . ' LIMIT ' . $offset . ',' . $limit );
            $stmt->execute( $params );
            $rows = $stmt->fetchAll( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : [];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function delete( string $table, array $args ) : bool {

        $where = implode( ' AND ', array_map( fn( $value ) => !is_array( $value[2] ) ? $value[0] . ' ' . $value[1] . ' ?' : $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')', $args ));

        $params = [];
        foreach( $args as $arg ) {
            if( is_array( $arg[2] )) {
                foreach( $arg[2] as $param ) {
                    $params[] = $param;
                }
            } else {
                $params[] = $arg[2];
            }
        }

        try {
            $stmt = $this->pdo->prepare( 'DELETE FROM ' . $table . ' WHERE ' . $where );
            $stmt->execute( $params );
            $rows = $stmt->rowCount();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }


    public function is_exists( string $table, array $args ) : bool {

        $where = implode( ' AND ', array_map( fn( $value ) => !is_array( $value[2] ) ? $value[0] . ' ' . $value[1] . ' ?' : $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')', $args ));

        $params = [];
        foreach( $args as $arg ) {
            if( is_array( $arg[2] )) {
                foreach( $arg[2] as $param ) {
                    $params[] = $param;
                }
            } else {
                $params[] = $arg[2];
            }
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT id FROM ' . $table . ' WHERE ' . $where . ' LIMIT 1' );
            $stmt->execute( $params );
            $rows = $stmt->fetch( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? !empty( $rows->id ) : false;
    }







}