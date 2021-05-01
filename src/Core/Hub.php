<?php
namespace App\Core;

class Hub extends \App\Core\Echidna
{
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

            if( $arg[0] == 'id' and empty( $arg[2] )) {
                $this->error = 'hub_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'hub_id is incorrect';
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

            } elseif( $arg[0] == 'user_id' and empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;

            } elseif( $arg[0] == 'user_id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'hub_status' and empty( $arg[2] )) {
                $this->error = 'hub_status is empty';
                break;

            } elseif( $arg[0] == 'hub_status' and !in_array( $arg[2], ['private', 'custom', 'trash'] )) {
                $this->error = 'hub_status is incorrect';
                break;

            } elseif( $arg[0] == 'hub_name' and empty( $arg[2] )) {
                $this->error = 'hub_name is empty';
                break;

            } elseif( $arg[0] == 'hub_name' and strlen( $arg[2] ) > 255 ) {
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

        if( empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';
    
        } elseif( !( is_string( $data['user_id'] ) and ctype_digit( $data['user_id'] )) and !( is_int( $data['user_id'] ) and $data['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( empty( $data['hub_status'] )) {
            $this->error = 'hub_status is empty';

        } elseif( !in_array( $data['hub_status'], ['private', 'custom', 'trash'] )) {
            $this->error = 'hub_status is incorrect';

        } elseif( empty( $data['hub_name'] )) {
            $this->error = 'hub_name is empty';

        } elseif( strlen( $data['hub_name'] ) > 255 ) {
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

        if( empty( $this->id )) {
            $this->error = 'hub_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'hub_id is incorrect';

        } elseif( !empty( $data['create_date'] ) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( !empty( $data['update_date'] ) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( !empty( $date['user_id'] ) and !ctype_digit( $date['user_id'] ) and !( is_int( $date['user_id'] ) and $date['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( !empty( $data['hub_status'] ) and !in_array( $data['hub_status'], ['private', 'custom', 'trash'] )) {
            $this->error = 'hub_status is incorrect';

        // TODO
        } elseif( array_key_exists('hub_name', $data) and empty( $data['hub_name'] )) {
            $this->error = 'hub_name is empty';

        } elseif( !empty( $data['hub_name'] ) and strlen( $data['hub_name'] ) > 255 ) {
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

        if( empty( $this->id )) {
            $this->error = 'hub_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'hub_id is incorrect';

        } elseif( !$this->delete( 'hubs', [['id', '=', $this->id]] )) {
            $this->error = 'attribute delete error';

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
