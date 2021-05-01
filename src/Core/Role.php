<?php
namespace App\Core;

class Role extends \App\Core\Echidna
{
    protected $error = '';

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

            if( $arg[0] == 'id' and empty( $arg[2] )) {
                $this->error = 'role_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'role_id is incorrect';
                break;

            } elseif( $arg[0] == 'create_date' and empty( $arg[2] )) {
                $this->error = 'create_date is empty';
                break;

            } elseif( $arg[0] == 'create_date' and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $arg[2] )) {
                $this->error = 'create_date is incorrect';
                break;

            } elseif( $arg[0] == 'update_date' and empty( $arg[2] )) {
                $this->error = 'update_date is empty';
                break;

            } elseif( $arg[0] == 'update_date' and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $arg[2] )) {
                $this->error = 'update_date is incorrect';
                break;

            } elseif( $arg[0] == 'hub_id' and empty( $arg[2] )) {
                $this->error = 'hub_id is empty';
                break;

            } elseif( $arg[0] == 'hub_id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'hub_id is incorrect';
                break;

            } elseif( $arg[0] == 'user_id' and empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;

            } elseif( $arg[0] == 'user_id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'user_role' and empty( $arg[2] )) {
                $this->error = 'user_role is empty';
                break;

            } elseif( $arg[0] == 'user_role' and !in_array( $arg[2], ['admin', 'editor', 'reader', 'invited'] )) {
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

        if( empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';
    
        } elseif( !( is_string( $data['hub_id'] ) and ctype_digit( $data['hub_id'] )) and !( is_int( $data['hub_id'] ) and $data['hub_id'] >= 0 )) {
            $this->error = 'hub_id is incorrect';

        } elseif( empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';
    
        } elseif( !( is_string( $data['user_id'] ) and ctype_digit( $data['user_id'] )) and !( is_int( $data['user_id'] ) and $data['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( empty( $data['user_role'] )) {
            $this->error = 'user_role is empty';

        } elseif( !in_array( $data['user_role'], ['admin', 'editor', 'reader', 'invited'] )) {
            $this->error = 'user_role is incorrect';

        } elseif( $this->is_exists( 'user_roles', [['user_id', '=', $data['user_id']], ['hub_id', '=', $data['hub_id']]] )) {
            $this->error = 'user_role is already exists';

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

        if( empty( $this->id )) {
            $this->error = 'role_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'role_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( array_key_exists('hub_id', $data) and empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';

        } elseif( array_key_exists('hub_id', $data) and !ctype_digit( $date['hub_id'] ) and !( is_int( $date['hub_id'] ) and $date['hub_id'] >= 0 )) {
            $this->error = 'hub_id is incorrect';

        } elseif( array_key_exists('user_id', $data) and empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';

        } elseif( array_key_exists('user_id', $data) and !ctype_digit( $date['user_id'] ) and !( is_int( $date['user_id'] ) and $date['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( array_key_exists('user_role', $data) and empty( $data['user_role'] )) {
            $this->error = 'user_role is empty';

        } elseif( array_key_exists('user_role', $data) and !in_array( $data['user_role'], ['admin', 'editor', 'reader', 'invited'] )) {
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

        if( empty( $this->id )) {
            $this->error = 'role_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
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
