<?php
namespace App\Core;

class Mapper
{
    protected $error;
    protected $repository;

    public function __construct( $repository ) {
        $this->error = '';
        $this->repository = $repository;
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
    }

    // +
    private function get_entity_params( $entity_class ) {
        $doc = $entity_class->getDocComment();
        return $this->parse_params( $doc, 'entity' );
    }

    // +
    private function get_property_params( $entity, $column ) {
        $class = new \ReflectionClass( $entity );
        $property = $class->getProperty( $column );
        $doc = $property->getDocComment();
        return $this->parse_params( $doc, 'column' );
    }

    // + doc format: @key(param1=value1 param2=value2)
    private function parse_params( $doc, $key ) {

        preg_match_all( '#@' . $key . '\((.*?)\)\n#s', $doc, $tmp );
        preg_match_all( '/\s*([^=]+)=(\S+)\s*/', !empty($tmp[1][0]) ? $tmp[1][0] : '', $tmp );
        return array_combine ( $tmp[1], $tmp[2] );
    }

    public function insert( $entity, $data ) {

        $this->error = '';
        $entity_class = new \ReflectionClass( $entity );
        $entity_params = $this->get_entity_params( $entity_class );

        foreach( $data as $key => $value ) {
            $property = $entity_class->getProperty( $key );
            $property->setAccessible( true );
            $property_params = $this->get_property_params( $entity, $key );

            if( $property_params[ 'nullable' ] != 'true' and empty( $value )) {
                $this->error = $key . ' is empty';
                break;

            } elseif( !empty( $value )) {

                if( !preg_match( $property_params[ 'regex' ], $value ) ) {
                    $this->error = $key . ' is incorrect';
                    break;

                } elseif( $property_params[ 'unique' ] == 'true' and $this->repository->is_exists( $entity_params[ 'table' ], [[ $key, '=', $value ]] ) ) {
                    $this->error = $key . ' is occupied';
                    break;
                }
            }
        }
        
        if( empty( $this->error )) {
            $data['id'] = $this->repository->insert( $entity_params['table'], $data );

            if( !empty( $data['id'] )) {

                foreach( $data as $key => $value ) {
                    $property = $entity_class->getProperty( $key );
                    $property->setAccessible( true );
                    $property->setValue( $entity, $value );
                }

            } else {
                $this->error = $entity_params['alias'] . ' insert error';
            }
        }

        return empty( $this->error );
    }

    public function update( $entity, $data ) {

        $this->error = '';
        $entity_class = new \ReflectionClass( $entity );
        $entity_params = $this->get_entity_params( $entity_class );

        foreach( $data as $key => $value ) {
            $property = $entity_class->getProperty( $key );
            $property->setAccessible( true );
            $property_params = $this->get_property_params( $entity, $key );

            if( $property_params[ 'nullable' ] != 'true' and empty( $value )) {
                $this->error = $key . ' is empty';
                break;

            } elseif( !empty( $value )) {

                if( !preg_match( $property_params[ 'regex' ], $value ) ) {
                    $this->error = $key . ' is incorrect';
                    break;

                } elseif( $property_params[ 'unique' ] == 'true' and $this->repository->is_exists( $entity_params[ 'table' ], [[ $key, '=', $value ]] ) ) {
                    $this->error = $key . ' is occupied';
                    break;
                }
            }
        }

        if( empty( $this->error )) {

            if( $this->repository->update( $entity_params['table'], [['id', '=', $entity->id]], $data )) {

                foreach( $data as $key => $value ) {
                    $property = $entity_class->getProperty( $key );
                    $property->setAccessible( true );
                    $property->setValue( $entity, $value );
                }

            } else {
                $this->error = $entity_params['alias'] . ' update error';
            }
        }

        return empty( $this->error );
    }

    public function delete( $entity ) {
        $this->error = '';

        $class = new \ReflectionClass( $entity );
        $params = $this->get_entity_params( $class );

        if( $this->repository->delete( $params['table'], [['id', '=', $entity->id]] )) {
            $properties = $class->getProperties();

            foreach( $properties as $property ) {
                //$property = $class->getProperty( $key );
                $property->setAccessible( true );
                $property->setValue( $entity, null );
            }

        } else {
            $this->error = $params['alias'] . ' delete error';
        }

        return empty( $this->error );
    }

    public function select( $entity, $args ) {
        $this->error = '';

        $class = new \ReflectionClass( $entity );
        $params = $this->get_entity_params( $class );
        $rows = $this->repository->select( ['*'], $params['table'], $args, 1, 0 );

        if( !empty( $rows )) {
            foreach( $rows[0] as $key=>$value ) {

                $property = $class->getProperty( $key );
                $property->setAccessible( true );
                $property->setValue( $entity, $rows[0]->$key );
            }

        } else {
            $this->error = $params['alias'] . ' not found';
        }

        return empty( $this->error );
    }



}
