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

    // doc format: @key(param1=value1 param2=value2)
    private function parse_params( $doc, $key ) {

        preg_match_all( '#@' . $key . '\((.*?)\)\n#s', $doc, $tmp );
        preg_match_all( '/\s*([^=]+)=(\S+)\s*/', !empty($tmp[1][0]) ? $tmp[1][0] : '', $tmp );
        return array_combine ( $tmp[1], $tmp[2] );
    }

    private function get_entity_params( $entity ) {

        $class = new \ReflectionClass( $entity );
        $doc = $class->getDocComment();
        return $this->parse_params( $doc, 'entity' );
    }

    private function get_column_params( $entity, $column ) {

        $class = new \ReflectionClass( $entity );
        $property = $class->getProperty( $column );
        $doc = $property->getDocComment();
        return $this->parse_params( $doc, 'column' );
    }

    public function save( $entity ) {

        $this->error = '';
        $entity_params = $this->get_entity_params( $entity );
        $class = new \ReflectionClass( $entity );
        $properties = $class->getProperties();
        $data = [];

        foreach( $properties as $property ) {
            $property_name = $property->name;
            $property = $class->getProperty( $property_name );
            $property->setAccessible( true );
            $property_value = $property->getValue( $entity );
            $property_params = $this->get_column_params( $entity, $property_name );

            if( !empty( $property_params )) {
                $property_name = array_key_exists( 'name', $property_params ) ? strval( $property_params['name'] ) : '';
                $property_type = array_key_exists( 'type', $property_params ) ? strval( $property_params['type'] ) : '';
                $property_length = array_key_exists( 'length', $property_params ) ? intval( $property_params['length'] ) : 0;
                $property_unique = array_key_exists( 'unique', $property_params ) ? boolval( $property_params['unique'] ) : false;
                $property_nullable = array_key_exists( 'nullable', $property_params ) ? boolval( $property_params['nullable'] ) : false;
                $property_regex = array_key_exists( 'regex', $property_params ) ? strval( $property_params['regex'] ) : '/^.*$/';

                if( !$property_nullable and empty( $property_value )) {
                    $this->error = $property_name . ' is empty';
                    break;

                } elseif( !empty( $property_value ) and $property_type != gettype( $property_value )) {
                    $this->error = $property_name . ' has incorrect type';
                    break;

                } elseif( !empty( $property_length ) and mb_strlen( $property_value ) > $property_length ) {
                    $this->error = $property_name . ' is too long';
                    break;

                } elseif( !empty( $property_regex ) and !preg_match( $property_regex, $property_value ) ) {
                    $this->error = $property_name . ' does not match the pattern';
                    break;

                } elseif( $property_unique and $this->repository->is_exists( $entity_params['table'], [['user_email', '=', $property_value]] ) ) {
                    $this->error = $property_name . ' is occupied';
                    break;

                } elseif( !empty( $property_value )) {
                    $data[$property_name] = $property_value;
                }
            }
        }


        if( empty( $this->error )) {
            if( empty( $this->id )) {
                $this->repository->insert( $entity_params['table'], $data );

            } else {
                //$this->repositoty->update( $entity );
            }
        }

    }
}
