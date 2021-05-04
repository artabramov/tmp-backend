<?php
namespace App\Core;

class Param extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $create_date;
    protected $update_date;
    protected $user_id;
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
                $this->error = 'param_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'param_id is incorrect';
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

            } elseif( $arg[0] == 'user_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;

            } elseif( $arg[0] == 'user_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'param_key' and $this->is_empty( $arg[2] )) {
                $this->error = 'param_key is empty';
                break;

            } elseif( $arg[0] == 'param_key' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'param_key is incorrect';
                break;

            } elseif( $arg[0] == 'param_value' and $this->is_empty( $arg[2] )) {
                $this->error = 'param_value is empty';
                break;

            } elseif( $arg[0] == 'param_value' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'param_value is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'user_params', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'param not found';
        
            } else {
                $this->id          = $rows[0]->id;
                $this->create_date = $rows[0]->create_date;
                $this->update_date = $rows[0]->update_date;
                $this->user_id     = $rows[0]->user_id;
                $this->param_key   = $rows[0]->param_key;
                $this->param_value = $rows[0]->param_value;
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

        } elseif( !array_key_exists('user_id', $data) or $this->is_empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';
    
        } elseif( !$this->is_num( $data['user_id'] )) {
            $this->error = 'user_id is incorrect';

        } elseif( !array_key_exists('param_key', $data) or $this->is_empty( $data['param_key'] )) {
            $this->error = 'param_key is empty';
    
        } elseif( !$this->is_string( $data['param_key'], 20 )) {
            $this->error = 'param_key is incorrect';

        } elseif( !array_key_exists('param_value', $data) or $this->is_empty( $data['param_value'] )) {
            $this->error = 'param_value is empty';
    
        } elseif( !$this->is_string( $data['param_value'], 255 )) {
            $this->error = 'param_value is incorrect';

        } elseif( $this->is_exists( 'user_params', [['user_id', '=', $data['user_id']], ['param_key', '=', $data['param_key']]] )) {
            $this->error = 'param_value is occupied';

        } else {
            $this->id = $this->insert( 'user_params', $data );

            if( empty( $this->id )) {
                $this->error = 'param insert error';

            } else {
                $this->create_date = $data['create_date'];
                $this->update_date = $data['update_date'];
                $this->user_id     = $data['user_id'];
                $this->param_key   = $data['param_key'];
                $this->param_value = $data['param_value'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'param_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'param_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and $this->is_empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !$this->is_datetime( $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and $this->is_empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !$this->is_datetime( $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( array_key_exists('user_id', $data) and $this->is_empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';

        } elseif( array_key_exists('user_id', $data) and !$this->is_num( $data['user_id'] )) {
            $this->error = 'user_id is incorrect';

        } elseif( array_key_exists('param_key', $data) and $this->is_empty( $data['param_key'] )) {
            $this->error = 'param_key is empty';

        } elseif( array_key_exists('param_key', $data) and !$this->is_string( $data['param_key'], 20 )) {
            $this->error = 'param_key is incorrect';

        } elseif( array_key_exists('param_value', $data) and $this->is_empty( $data['param_value'] )) {
            $this->error = 'param_value is empty';

        } elseif( array_key_exists('param_value', $data) and !$this->is_string( $data['param_value'], 255 )) {
            $this->error = 'param_value is incorrect';

        } elseif( !$this->update( 'user_params', [['id', '=', $this->id]], $data )) {
            $this->error = 'param update error';

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
            $this->error = 'param_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'param_id is incorrect';

        } elseif( !$this->delete( 'user_params', [['id', '=', $this->id]] )) {
            $this->error = 'param delete error';

        } else {
            $this->id          = null;
            $this->create_date = null;
            $this->update_date = null;
            $this->user_id     = null;
            $this->param_key   = null;
            $this->param_value = null;
        }

        return empty( $this->error );
    }

}
