<?php
namespace App\Core;

class Meta extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $create_date;
    protected $update_date;
    protected $parent_type;
    protected $parent_id;
    protected $param_key;
    protected $param_value;

    public function __get( string $key ) {

        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return false;
    }

    public function __isset( string $key ) {

        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    public function get( array $args ) : bool {

        foreach( $args as $arg ) {

            if( $arg[0] == 'id' and $this->is_empty( $arg[2] )) {
                $this->error = 'meta_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'meta_id is incorrect';
                break;

            } elseif( $arg[0] == 'create_date' and $this->is_empty( $arg[2] )) {
                $this->error = 'create_date is empty';
                break;

            } elseif( $arg[0] == 'create_date' and !$this->is_datetime( $arg[2] )) {
                $this->error = 'create_date is incorrect';
                break;

            } elseif( $arg[0] == 'update_date' and $this->is_empty( $arg[2] )) {
                $this->error = 'update_date is empty';
                break;

            } elseif( $arg[0] == 'update_date' and !$this->is_datetime( $arg[2] )) {
                $this->error = 'update_date is incorrect';
                break;

            } elseif( $arg[0] == 'parent_type' and $this->is_empty( $arg[2] )) {
                $this->error = 'parent_type is empty';
                break;

            } elseif( $arg[0] == 'parent_type' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'parent_type is incorrect';
                break;

            } elseif( $arg[0] == 'parent_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'parent_id is empty';
                break;

            } elseif( $arg[0] == 'parent_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'parent_id is incorrect';
                break;

            } elseif( $arg[0] == 'meta_key' and $this->is_empty( $arg[2] )) {
                $this->error = 'meta_key is empty';
                break;

            } elseif( $arg[0] == 'meta_key' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'meta_key is incorrect';
                break;

            } elseif( $arg[0] == 'meta_value' and $this->is_empty( $arg[2] )) {
                $this->error = 'meta_value is empty';
                break;

            } elseif( $arg[0] == 'meta_value' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'meta_value is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'meta', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'meta not found';
        
            } else {
                $this->id          = $rows[0]->id;
                $this->create_date = $rows[0]->create_date;
                $this->update_date = $rows[0]->update_date;
                $this->parent_type = $rows[0]->parent_type;
                $this->parent_id   = $rows[0]->parent_id;
                $this->meta_key    = $rows[0]->meta_key;
                $this->meta_value  = $rows[0]->meta_value;
            }
        }

        return empty( $this->error );
    }

    public function set( array $data ) : bool {

        if( !array_key_exists('create_date', $data) or $this->is_empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( !$this->is_datetime( $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( !array_key_exists('update_date', $data) or $this->is_empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( !$this->is_datetime( $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( !array_key_exists('parent_type', $data) or $this->is_empty( $data['parent_type'] )) {
            $this->error = 'parent_type is empty';
    
        } elseif( !$this->is_string( $data['parent_type'], 20 )) {
            $this->error = 'parent_type is incorrect';


        } elseif( !array_key_exists('parent_id', $data) or $this->is_empty( $data['parent_id'] )) {
            $this->error = 'parent_id is empty';
    
        } elseif( !$this->is_num( $data['parent_id'] )) {
            $this->error = 'parent_id is incorrect';

        } elseif( !array_key_exists('meta_key', $data) or $this->is_empty( $data['meta_key'] )) {
            $this->error = 'meta_key is empty';
    
        } elseif( !$this->is_string( $data['meta_key'], 20 )) {
            $this->error = 'meta_key is incorrect';

        } elseif( !array_key_exists('meta_value', $data) or $this->is_empty( $data['meta_value'] )) {
            $this->error = 'meta_value is empty';
    
        } elseif( !$this->is_string( $data['meta_value'], 255 )) {
            $this->error = 'meta_value is incorrect';

        } elseif( $this->is_exists( 'meta', [['parent_type', '=', $data['parent_type']], ['parent_id', '=', $data['parent_id']], ['meta_key', '=', $data['meta_key']]] )) {
            $this->error = 'meta_value is occupied';

        } else {
            $this->id = $this->insert( 'meta', $data );

            if( empty( $this->id )) {
                $this->error = 'meta insert error';

            } else {
                $this->create_date = $data['create_date'];
                $this->update_date = $data['update_date'];
                $this->parent_type = $data['parent_type'];
                $this->parent_id   = $data['parent_id'];
                $this->meta_key    = $data['meta_key'];
                $this->meta_value  = $data['meta_value'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'meta_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'meta_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and $this->is_empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !$this->is_datetime( $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and $this->is_empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !$this->is_datetime( $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( array_key_exists('parent_type', $data) and $this->is_empty( $data['parent_type'] )) {
            $this->error = 'parent_type is empty';

        } elseif( array_key_exists('parent_type', $data) and !$this->is_string( $data['parent_type'], 20 )) {
            $this->error = 'parent_type is incorrect';

        } elseif( array_key_exists('parent_id', $data) and $this->is_empty( $data['parent_id'] )) {
            $this->error = 'parent_id is empty';

        } elseif( array_key_exists('parent_id', $data) and !$this->is_num( $data['parent_id'] )) {
            $this->error = 'parent_id is incorrect';

        } elseif( array_key_exists('meta_key', $data) and $this->is_empty( $data['meta_key'] )) {
            $this->error = 'meta_key is empty';

        } elseif( array_key_exists('meta_key', $data) and !$this->is_string( $data['meta_key'], 20 )) {
            $this->error = 'meta_key is incorrect';

        } elseif( array_key_exists('meta_value', $data) and $this->is_empty( $data['meta_value'] )) {
            $this->error = 'meta_value is empty';

        } elseif( array_key_exists('meta_value', $data) and !$this->is_string( $data['meta_value'], 255 )) {
            $this->error = 'meta_value is incorrect';

        } elseif( !$this->update( 'meta', [['id', '=', $this->id]], $data )) {
            $this->error = 'meta update error';

        } else {
            foreach( $data as $key => $value ) {
                if( !in_array( $key, ['pdo', 'e', 'error'] ) and property_exists( $this, $key )) {
                    $this->$key = $value;
                }
            }
        }

        return empty( $this->error );
    }

    public function del() : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'meta_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'meta_id is incorrect';

        } elseif( !$this->delete( 'meta', [['id', '=', $this->id]] )) {
            $this->error = 'meta delete error';

        } else {
            $this->id          = null;
            $this->create_date = null;
            $this->update_date = null;
            $this->parent_type = null;
            $this->parent_id   = null;
            $this->meta_key    = null;
            $this->meta_value  = null;
        }

        return empty( $this->error );
    }

}
