<?php
namespace App\Core;

class Echidna
{
    protected $pdo;
    protected $e;

    public function __construct( \PDO $pdo ) {
        $this->pdo = $pdo;
        $this->e = null;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function is_exists( string $table, array $args ) : bool {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT id FROM ' . $table . ' ' . $where . ' LIMIT 1' );
            foreach( $args as $arg ) {
                $stmt->bindParam( ':' . $arg[0], $arg[2]  );

                /*
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_STR );
                }
                */
            }

            $stmt->execute();
            $rows = $stmt->fetch( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? !empty( $rows->id ) : false;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function insert( string $table, array $data ) : int {

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

                //$stmt->bindParam( ':' . $key, $data[ $key ], $this->pdo::PARAM_STR );
            }

            $stmt->execute();
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
    protected function update( string $table, array $args, array $data ) : bool {

        $set = '';
        foreach( $data as $key=>$value ) {
            $set .= empty( $set ) ? 'SET ' : ', ';
            $set .= $key . '=:' . $key;
        }

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'UPDATE ' . $table . ' ' . $set . ' ' . $where . ' LIMIT 1' );

            foreach( $args as $arg ) {
                $stmt->bindParam( ':' . $arg[0], $arg[2] );

                /*
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], $this->pdo::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_STR );
                }
                */
            }

            foreach( $data as $key=>&$value ) {
                $stmt->bindParam( ':' . $key, $value, $this->pdo::PARAM_STR );
            }

            $stmt->execute();
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
    protected function select( string $fields, string $table, array $args, int $limit, int $offset ) : array {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT ' . $fields . ' FROM ' . $table . ' ' . $where . ' LIMIT :limit OFFSET :offset' );

            foreach( $args as $arg ) {
                $stmt->bindParam( ':' . $arg[0], $arg[2] );

                /*
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], $this->pdo::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_STR );
                }
                */
            }

            $stmt->bindValue( ':limit', $limit, $this->pdo::PARAM_INT );
            $stmt->bindValue( ':offset', $offset, $this->pdo::PARAM_INT );

            $stmt->execute();
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

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'DELETE FROM ' . $table . ' ' . $where . ' LIMIT 1' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], $this->pdo::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows = $stmt->rowCount();

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function count( string $table, array $args ) : int {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT COUNT(id) FROM ' . $table . ' ' . $where );
            foreach( $args as $arg ) {

                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], $this->pdo::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows = $stmt->fetch( $this->pdo::FETCH_ASSOC );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows[ 'COUNT(id)' ] : 0;
    }

    protected function query( string $query, array $data = [] ) {

        try {
            $stmt = $this->pdo->prepare( $query );
            foreach( $data as $key=>$value ) {
                $stmt->bindParam( ':' . $key, $data[ $key ] );
            }
            $stmt->execute();
            $rows = $stmt->fetchAll( $this->pdo::FETCH_OBJ );

        } catch( \Exception $e ) {
            $this->e = $e;
        }

        return $rows;
    }

}