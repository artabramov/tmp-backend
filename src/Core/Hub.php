<?php
namespace App\Core;

class Hub extends \App\Core\Echidna
{
    //protected const HUB_STATUSES = [ 'private', 'custom', 'trash' ];

    protected $error;

    protected $id;
    protected $create_date;
    protected $update_date;
    protected $user_id;
    protected $hub_status;
    protected $hub_name;

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
                $this->error = 'hub_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'hub_id is incorrect';
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

            } elseif( $arg[0] == 'hub_status' and $this->is_empty( $arg[2] )) {
                $this->error = 'hub_status is empty';
                break;

            } elseif( $arg[0] == 'hub_status' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'hub_status is incorrect';
                break;

            } elseif( $arg[0] == 'hub_name' and $this->is_empty( $arg[2] )) {
                $this->error = 'hub_name is empty';
                break;

            } elseif( $arg[0] == 'hub_name' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'hub_name is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'hubs', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'hub not found';
        
            } else {
                $this->id          = $rows[0]->id;
                $this->create_date = $rows[0]->create_date;
                $this->update_date = $rows[0]->update_date;
                $this->user_id     = $rows[0]->user_id;
                $this->hub_status  = $rows[0]->hub_status;
                $this->hub_name    = $rows[0]->hub_name;
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

        } elseif( !array_key_exists('hub_status', $data) or $this->is_empty( $data['hub_status'] )) {
            $this->error = 'hub_status is empty';

        } elseif( !$this->is_string( $data['hub_status'], 20 )) {
            $this->error = 'hub_status is incorrect';

        } elseif( !array_key_exists('hub_name', $data) or $this->is_empty( $data['hub_name'] )) {
            $this->error = 'hub_name is empty';

        } elseif( !$this->is_string( $data['hub_name'], 255 )) {
            $this->error = 'hub_name is incorrect';

        } else {
            $this->id = $this->insert( 'hubs', $data );

            if( empty( $this->id )) {
                $this->error = 'hub insert error';

            } else {
                $this->create_date = $data['create_date'];
                $this->update_date = $data['update_date'];
                $this->user_id     = $data['user_id'];
                $this->hub_status  = $data['hub_status'];
                $this->hub_name    = $data['hub_name'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'hub_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'hub_id is incorrect';

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

        } elseif( array_key_exists('hub_status', $data) and $this->is_empty( $data['hub_status'] )) {
            $this->error = 'hub_status is empty';

        } elseif( array_key_exists('hub_status', $data) and !$this->is_string( $data['hub_status'], 20 )) {
            $this->error = 'hub_status is incorrect';

        } elseif( array_key_exists('hub_name', $data) and $this->is_empty( $data['hub_name'] )) {
            $this->error = 'hub_name is empty';

        } elseif( array_key_exists('hub_name', $data) and !$this->is_string( $data['hub_name'], 255 )) {
            $this->error = 'hub_name is incorrect';

        } elseif( !$this->update( 'hubs', [['id', '=', $this->id]], $data )) {
            $this->error = 'hub update error';

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
            $this->error = 'hub_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'hub_id is incorrect';

        } elseif( !$this->delete( 'hubs', [['id', '=', $this->id]] )) {
            $this->error = 'hub delete error';

        } else {
            $this->id          = null;
            $this->create_date = null;
            $this->update_date = null;
            $this->user_id     = null;
            $this->hub_status  = null;
            $this->hub_name    = null;
        }

        return empty( $this->error );
    }


}
