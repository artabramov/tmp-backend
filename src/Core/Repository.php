<?php
namespace App\Core;

class Repository
{
    protected $e;
    protected $pdo;

    public function __construct( $pdo ) {
        $this->e = null;
        $this->pdo = $pdo;
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

    protected function get_where( array $args ) : string {
        return implode( ' AND ', array_map( fn( $value ) => !is_array( $value[2] ) ? $value[0] . ' ' . $value[1] . ' ?' : $value[0] . ' ' . $value[1] . ' (' . implode( ', ', array_map( fn() => '?', $value[2] ) ) . ')', $args ));
    }

    protected function get_params( array $args ) : array {

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

        return $params;
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
            $this->e = $e;
        }

        return empty( $this->e ) ? $id : 0;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function update( string $table, array $args, array $data ) : bool {

        $set = implode( ', ', array_map( fn( $value ) => $value . ' = ?', array_keys( $data )));
        $where = $this->get_where( $args );
        $params = array_merge( array_values( $data ), $this->get_params( $args ));

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
        $where = $this->get_where( $args );
        $params = $this->get_params( $args );

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
    public function delete( string $table, array $args ) : bool {

        $where = $this->get_where( $args );
        $params = $this->get_params( $args );

        try {
            $stmt = $this->pdo->prepare( 'DELETE FROM ' . $table . ' WHERE ' . $where );
            $stmt->execute( $params );
            $rows = $stmt->rowCount();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function time() {

        try {
            $stmt = $this->pdo->prepare( 'SELECT NOW() AS datetime;' );
            $stmt->execute();
            $rows = $stmt->fetch( $this->pdo::FETCH_ASSOC );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows[ 'datetime' ] : '0000-00-00 00:00:00';
    }

}
