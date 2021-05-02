<?php
namespace App\Core;

class Role extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $create_date;
    protected $update_date;
    protected $hub_id;
    protected $user_id;
    protected $user_role;

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
                $this->error = 'role_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'role_id is incorrect';
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

            } elseif( $arg[0] == 'hub_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'hub_id is empty';
                break;

            } elseif( $arg[0] == 'hub_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'hub_id is incorrect';
                break;

            } elseif( $arg[0] == 'user_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;

            } elseif( $arg[0] == 'user_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'user_role' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_role is empty';
                break;

            } elseif( $arg[0] == 'user_role' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'user_role is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'user_roles', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'role not found';
        
            } else {
                $this->id          = $rows[0]->id;
                $this->create_date = $rows[0]->create_date;
                $this->update_date = $rows[0]->update_date;
                $this->hub_id      = $rows[0]->hub_id;
                $this->user_id     = $rows[0]->user_id;
                $this->user_role   = $rows[0]->user_role;
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

        } elseif( !array_key_exists('hub_id', $data) or $this->is_empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';
    
        } elseif( !$this->is_num( $data['hub_id'] )) {
            $this->error = 'hub_id is incorrect';

        } elseif( !array_key_exists('user_id', $data) or $this->is_empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';
    
        } elseif( !$this->is_num( $data['user_id'] )) {
            $this->error = 'user_id is incorrect';

        } elseif( !array_key_exists('user_role', $data) or $this->is_empty( $data['user_role'] )) {
            $this->error = 'user_role is empty';
    
        } elseif( !$this->is_string( $data['user_role'], 20 )) {
            $this->error = 'user_role is incorrect';

        } elseif( $this->is_exists( 'user_roles', [['user_id', '=', $data['user_id']], ['hub_id', '=', $data['hub_id']]] )) {
            $this->error = 'user_role is occupied';

        } else {
            $this->id = $this->insert( 'user_roles', $data );

            if( empty( $this->id )) {
                $this->error = 'role insert error';

            } else {
                $this->create_date = $data['create_date'];
                $this->update_date = $data['update_date'];
                $this->hub_id      = $data['hub_id'];
                $this->user_id     = $data['user_id'];
                $this->user_role   = $data['user_role'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'role_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'role_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and $this->is_empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !$this->is_datetime( $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and $this->is_empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !$this->is_datetime( $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( array_key_exists('hub_id', $data) and $this->is_empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';

        } elseif( array_key_exists('hub_id', $data) and !$this->is_num( $data['hub_id'] )) {
            $this->error = 'hub_id is incorrect';

        } elseif( array_key_exists('user_id', $data) and $this->is_empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';

        } elseif( array_key_exists('user_id', $data) and !$this->is_num( $data['user_id'] )) {
            $this->error = 'user_id is incorrect';

        } elseif( array_key_exists('user_role', $data) and $this->is_empty( $data['user_role'] )) {
            $this->error = 'user_role is empty';

        } elseif( array_key_exists('user_role', $data) and !$this->is_string( $data['user_role'], 20 )) {
            $this->error = 'user_role is incorrect';

        } elseif( !$this->update( 'user_roles', [['id', '=', $this->id]], $data )) {
            $this->error = 'role update error';

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
            $this->error = 'role_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'role_id is incorrect';

        } elseif( !$this->delete( 'user_roles', [['id', '=', $this->id]] )) {
            $this->error = 'role delete error';

        } else {
            $this->id          = null;
            $this->create_date = null;
            $this->update_date = null;
            $this->hub_id      = null;
            $this->user_id     = null;
            $this->user_role   = null;
        }

        return empty( $this->error );
    }

}
