<?php
namespace App\Core;

class Meta extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $create_date;
    protected $update_date;
    protected $user_id;
    protected $post_id;
    protected $meta_key;
    protected $meta_value;

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
                $this->error = 'meta_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'meta_id is incorrect';
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

            } elseif( $arg[0] == 'post_id' and empty( $arg[2] )) {
                $this->error = 'post_id is empty';
                break;

            } elseif( $arg[0] == 'post_id' and !( is_string( $arg[2] ) and ctype_digit( $arg[2] )) and !( is_int( $arg[2] ) and $arg[2] >= 0 )) {
                $this->error = 'post_id is incorrect';
                break;

            } elseif( $arg[0] == 'meta_key' and empty( $arg[2] )) {
                $this->error = 'meta_key is empty';
                break;

            } elseif( $arg[0] == 'meta_key' and strlen( $arg[2] ) > 20 ) {
                $this->error = 'meta_key is incorrect';
                break;

            } elseif( $arg[0] == 'meta_value' and empty( $arg[2] )) {
                $this->error = 'meta_value is empty';
                break;

            } elseif( $arg[0] == 'meta_value' and strlen( $arg[2] ) > 255 ) {
                $this->error = 'meta_value is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'post_meta', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'meta not found';
        
            } else {
                $this->id          = $rows[0]->id;
                $this->create_date = $rows[0]->create_date;
                $this->update_date = $rows[0]->update_date;
                $this->user_id     = $rows[0]->user_id;
                $this->post_id     = $rows[0]->post_id;
                $this->meta_key    = $rows[0]->meta_key;
                $this->meta_value  = $rows[0]->meta_value;
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

        } elseif( empty( $data['post_id'] )) {
            $this->error = 'post_id is empty';
    
        } elseif( !( is_string( $data['post_id'] ) and ctype_digit( $data['post_id'] )) and !( is_int( $data['post_id'] ) and $data['post_id'] >= 0 )) {
            $this->error = 'post_id is incorrect';

        } elseif( empty( $data['meta_key'] )) {
            $this->error = 'meta_key is empty';

        } elseif( strlen( $data['meta_key'] ) > 20 ) {
            $this->error = 'meta_key is incorrect';

        } elseif( empty( $data['meta_value'] )) {
            $this->error = 'meta_value is empty';

        } elseif( strlen( $data['meta_value'] ) > 255 ) {
            $this->error = 'meta_value is incorrect';

        } else {
            $this->id = $this->insert( 'post_meta', $data );

            if( empty( $this->id )) {
                $this->error = 'meta insert error';

            } else {
                $this->create_date = $data['create_date'];
                $this->update_date = $data['update_date'];
                $this->user_id     = $data['user_id'];
                $this->post_id     = $data['post_id'];
                $this->meta_key    = $data['meta_key'];
                $this->meta_value  = $data['meta_value'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( empty( $this->id )) {
            $this->error = 'meta_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'meta_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( array_key_exists('user_id', $data) and empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';

        } elseif( array_key_exists('user_id', $data) and !ctype_digit( $date['user_id'] ) and !( is_int( $date['user_id'] ) and $date['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( array_key_exists('post_id', $data) and empty( $data['post_id'] )) {
            $this->error = 'post_id is empty';

        } elseif( array_key_exists('post_id', $data) and !ctype_digit( $date['post_id'] ) and !( is_int( $date['post_id'] ) and $date['post_id'] >= 0 )) {
            $this->error = 'post_id is incorrect';

        } elseif( array_key_exists('meta_key', $data) and empty( $data['meta_key'] )) {
            $this->error = 'meta_key is empty';

        } elseif( array_key_exists('meta_key', $data) and strlen( $data['meta_key'] ) > 20 ) {
            $this->error = 'meta_key is incorrect';

        } elseif( array_key_exists('meta_value', $data) and empty( $data['meta_value'] )) {
            $this->error = 'meta_value is empty';

        } elseif( array_key_exists('meta_value', $data) and strlen( $data['meta_value'] ) > 255 ) {
            $this->error = 'meta_value is incorrect';

        } elseif( !$this->update( 'post_meta', [['id', '=', $this->id]], $data )) {
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

        if( empty( $this->id )) {
            $this->error = 'meta_id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'meta_id is incorrect';

        } elseif( !$this->delete( 'post_meta', [['id', '=', $this->id]] )) {
            $this->error = 'meta delete error';

        } else {
            $this->id          = null;
            $this->create_date = null;
            $this->update_date = null;
            $this->user_id     = null;
            $this->post_id     = null;
            $this->meta_key    = null;
            $this->meta_value  = null;
        }

        return empty( $this->error );
    }

}
