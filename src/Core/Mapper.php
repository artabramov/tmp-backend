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

    // get param value from array
    private function get_param( $key, $array ) {

        if( !array_key_exists( $key, $array )) {
            return null;
        }

        if( in_array( $array[ $key ], [ 'true', 'false' ] ) ) {
            return $array[ $key ] == 'true' ? true : false;

        } elseif( ctype_digit( $array[ $key ] )) {
            return intval( $array[ $key ] );

        } else {
            return strval( $array[ $key ] );
        }
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
                $property_unique = $this->get_param( 'unique', $property_params );
                $property_nullable = $this->get_param( 'nullable', $property_params );
                $property_regex = $this->get_param( 'regex', $property_params );

                if( $property_nullable !== true and empty( $property_value )) {
                    $this->error = $property_name . ' is empty';
                    break;

                } elseif( !empty( $property_value )) {

                    if( !empty( $property_regex ) and !preg_match( $property_regex, $property_value ) ) {
                        $this->error = $property_name . ' is incorrect';
                        break;

                    } elseif( $property_unique === true and $this->repository->is_exists( $entity_params['table'], [['user_email', '=', $property_value]] ) ) {
                        $this->error = $property_name . ' is occupied';
                        break;

                    } elseif( !empty( $property_value )) {
                        $data[$property_name] = $property_value;
                    }
                }
            }
        }

        if( empty( $this->error )) {

            $property_id = $class->getProperty( 'id' );
            $property_id->setAccessible( true );
            $entity_id = $property_id->getValue( $entity );

            if( empty( $entity_id )) {
                $id = $this->repository->insert( $entity_params['table'], $data );

                if( !empty( $id )) {
                    $property_id->setValue( $entity, $id );

                } else {
                    $this->error = $entity_params['alias'] . ' save error';
                }

            } else {
                //$this->repositoty->update( $entity );
            }
        }

        return empty( $this->error );
    }
}
