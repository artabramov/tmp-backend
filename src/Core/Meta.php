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
            $this->error = 'id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'id is incorrect';

        } elseif( !empty( $data['create_date'] ) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( !empty( $data['update_date'] ) and !preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( !empty( $date['user_id'] ) and !ctype_digit( $date['user_id'] ) and !( is_int( $date['user_id'] ) and $date['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( !empty( $date['post_id'] ) and !ctype_digit( $date['post_id'] ) and !( is_int( $date['post_id'] ) and $date['post_id'] >= 0 )) {
            $this->error = 'post_id is incorrect';

        } elseif( !empty( $data['meta_key'] ) and strlen( $data['meta_key'] ) > 20 ) {
            $this->error = 'meta_key is incorrect';

        } elseif( !empty( $data['meta_value'] ) and strlen( $data['meta_value'] ) > 255 ) {
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
            $this->error = 'id is empty';

        } elseif( !( is_string( $this->id ) and ctype_digit( $this->id )) and !( is_int( $this->id ) and $this->id >= 0 )) {
            $this->error = 'id is incorrect';

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
