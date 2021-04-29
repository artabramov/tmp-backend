<?php
namespace App\Core;

class Post extends \App\Core\Echidna
{
    protected $error;

    protected $id;
    protected $create_date;
    protected $update_date;
    protected $parent_id;
    protected $user_id;
    protected $hub_id;
    protected $post_type;
    protected $post_status;
    protected $post_content;

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

        $rows = $this->select( '*', 'posts', $args, 1, 0 );

        if ( empty( $rows[0] )) {
            $this->error = 'post not found';
    
        } else {
            $this->id           = $rows[0]->id;
            $this->create_date  = $rows[0]->create_date;
            $this->update_date  = $rows[0]->update_date;
            $this->parent_id    = $rows[0]->parent_id;
            $this->user_id      = $rows[0]->user_id;
            $this->hub_id       = $rows[0]->hub_id;
            $this->post_type    = $rows[0]->post_type;
            $this->post_status  = $rows[0]->post_status;
            $this->post_content = $rows[0]->post_content;
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

        // parent_id can be empty
        //} elseif( empty( $data['parent_id'] )) {
        //    $this->error = 'parent_id is empty';
    
        } elseif( !empty( $data['parent_id'] ) and !( is_string( $data['parent_id'] ) and ctype_digit( $data['parent_id'] )) and !( is_int( $data['parent_id'] ) and $data['parent_id'] >= 0 )) {
            $this->error = 'parent_id is incorrect';

        } elseif( empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';
    
        } elseif( !( is_string( $data['user_id'] ) and ctype_digit( $data['user_id'] )) and !( is_int( $data['user_id'] ) and $data['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';
    
        } elseif( !( is_string( $data['hub_id'] ) and ctype_digit( $data['hub_id'] )) and !( is_int( $data['hub_id'] ) and $data['hub_id'] >= 0 )) {
            $this->error = 'hub_id is incorrect';

        } elseif( empty( $data['post_type'] )) {
            $this->error = 'post_type is empty';

        } elseif( !in_array( $data['post_type'], ['document', 'comment'] )) {
            $this->error = 'post_type is incorrect';

        } elseif( empty( $data['post_status'] )) {
            $this->error = 'post_status is empty';

        } elseif( !in_array( $data['post_status'], ['draft', 'todo', 'doing', 'done', 'inherit', 'trash'] )) {
            $this->error = 'post_status is incorrect';

        } elseif( empty( $data['post_content'] )) {
            $this->error = 'post_content is empty';

        } else {
            $this->id = $this->insert( 'posts', $data );

            if( empty( $this->id )) {
                $this->error = 'post insert error';

            } else {
                $this->create_date  = $data['create_date'];
                $this->update_date  = $data['update_date'];
                $this->parent_id    = $data['parent_id'];
                $this->user_id      = $data['user_id'];
                $this->hub_id       = $data['hub_id'];
                $this->post_type    = $data['post_type'];
                $this->post_status  = $data['post_status'];
                $this->post_content = $data['post_content'];
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

        } elseif( !empty( $date['parent_id'] ) and !ctype_digit( $date['parent_id'] ) and !( is_int( $date['parent_id'] ) and $date['parent_id'] >= 0 )) {
            $this->error = 'parent_id is incorrect';

        } elseif( !empty( $date['user_id'] ) and !ctype_digit( $date['user_id'] ) and !( is_int( $date['user_id'] ) and $date['user_id'] >= 0 )) {
            $this->error = 'user_id is incorrect';

        } elseif( !empty( $date['hub_id'] ) and !ctype_digit( $date['hub_id'] ) and !( is_int( $date['hub_id'] ) and $date['hub_id'] >= 0 )) {
            $this->error = 'hub_id is incorrect';

        } elseif( !empty( $data['post_type'] ) and !in_array( $data['post_type'], ['document', 'comment'] )) {
            $this->error = 'post_type is incorrect';

        } elseif( !empty( $data['post_status'] ) and !in_array( $data['post_status'], ['draft', 'todo', 'doing', 'done', 'inherit', 'trash'] )) {
            $this->error = 'post_status is incorrect';

        } elseif( !$this->update( 'posts', [['id', '=', $this->id]], $data )) {
            $this->error = 'post update error';

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

        } elseif( !$this->delete( 'posts', [['id', '=', $this->id]] )) {
            $this->error = 'post delete error';

        } else {
            $this->id           = null;
            $this->create_date  = null;
            $this->update_date  = null;
            $this->parent_id    = null;
            $this->user_id      = null;
            $this->hub_id       = null;
            $this->post_type    = null;
            $this->post_status  = null;
            $this->post_content = null;
        }

        return empty( $this->error );
    }

}
