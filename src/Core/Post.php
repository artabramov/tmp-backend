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
    protected $childs_count;

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
                $this->error = 'post_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'post_id is incorrect';
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

            // parent_id can be empty
            } elseif( $arg[0] == 'parent_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'parent_id is incorrect';
                break;

            } elseif( $arg[0] == 'user_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'user_id is empty';
                break;

            } elseif( $arg[0] == 'user_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'user_id is incorrect';
                break;

            } elseif( $arg[0] == 'hub_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'hub_id is empty';
                break;

            } elseif( $arg[0] == 'hub_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'hub_id is incorrect';
                break;

            } elseif( $arg[0] == 'post_type' and $this->is_empty( $arg[2] )) {
                $this->error = 'post_type is empty';
                break;

            } elseif( $arg[0] == 'post_type' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'post_type is incorrect';
                break;

            } elseif( $arg[0] == 'post_status' and $this->is_empty( $arg[2] )) {
                $this->error = 'post_status is empty';
                break;

            } elseif( $arg[0] == 'post_status' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'post_status is incorrect';
                break;

            } elseif( $arg[0] == 'post_content' and $this->is_empty( $arg[2] )) {
                $this->error = 'post_content is empty';
                break;

            } elseif( $arg[0] == 'post_content' and !$this->is_string( $arg[2], 0 )) {
                $this->error = 'post_content is incorrect';
                break;

            // childs_count can be empty
            } elseif( $arg[0] == 'childs_count' and !$this->is_num( $arg[2] )) {
                $this->error = 'childs_count is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
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
                $this->childs_count = $rows[0]->childs_count;
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

        // parent_id can be empty
        } elseif( array_key_exists('parent_id', $data) and !$this->is_num( $data['parent_id'] )) {
            $this->error = 'parent_id is incorrect';

        } elseif( !array_key_exists('user_id', $data) or $this->is_empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';
    
        } elseif( !$this->is_num( $data['user_id'] )) {
            $this->error = 'user_id is incorrect';

        } elseif( !array_key_exists('hub_id', $data) or $this->is_empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';
    
        } elseif( !$this->is_num( $data['hub_id'] )) {
            $this->error = 'hub_id is incorrect';

        } elseif( !array_key_exists('post_type', $data) or $this->is_empty( $data['post_type'] )) {
            $this->error = 'post_type is empty';
    
        } elseif( !$this->is_string( $data['post_type'], 20 )) {
            $this->error = 'post_type is incorrect';

        } elseif( !array_key_exists('post_status', $data) or $this->is_empty( $data['post_status'] )) {
            $this->error = 'post_status is empty';
    
        } elseif( !$this->is_string( $data['post_status'], 20 )) {
            $this->error = 'post_status is incorrect';

        } elseif( !array_key_exists('post_content', $data) or $this->is_empty( $data['post_content'] )) {
            $this->error = 'post_content is empty';

        } elseif( !$this->is_string( $data['post_content'], 0 )) {
            $this->error = 'post_content is incorrect';

        // childs_count can be empty
        } elseif( !$this->is_num( $data['childs_count'] )) {
            $this->error = 'childs_count is incorrect';

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
                $this->childs_count = $data['childs_count'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'post_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'post_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and $this->is_empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !$this->is_datetime( $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and $this->is_empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !$this->is_datetime( $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        // parent_id can be empty
        } elseif( array_key_exists('parent_id', $data) and !$this->is_num( $data['parent_id'] )) {
            $this->error = 'parent_id is incorrect';

        } elseif( array_key_exists('user_id', $data) and $this->is_empty( $data['user_id'] )) {
            $this->error = 'user_id is empty';

        } elseif( array_key_exists('user_id', $data) and !$this->is_num( $data['user_id'] )) {
            $this->error = 'user_id is incorrect';

        } elseif( array_key_exists('hub_id', $data) and $this->is_empty( $data['hub_id'] )) {
            $this->error = 'hub_id is empty';

        } elseif( array_key_exists('hub_id', $data) and !$this->is_num( $data['hub_id'] )) {
            $this->error = 'hub_id is incorrect';

        } elseif( array_key_exists('post_type', $data) and $this->is_empty( $data['post_type'] )) {
            $this->error = 'post_type is empty';

        } elseif( array_key_exists('post_type', $data) and !$this->is_string( $data['post_type'], 20 )) {
            $this->error = 'post_type is incorrect';

        } elseif( array_key_exists('post_status', $data) and $this->is_empty( $data['post_status'] )) {
            $this->error = 'post_status is empty';

        } elseif( array_key_exists('post_status', $data) and !$this->is_string( $data['post_status'], 20 )) {
            $this->error = 'post_status is incorrect';

        } elseif( array_key_exists('post_content', $data) and !$this->is_string( $data['post_content'], 0 )) {
            $this->error = 'post_content is incorrect';

        // childs_count can be empty
        } elseif( array_key_exists('childs_count', $data) and !$this->is_num( $data['childs_count'] )) {
            $this->error = 'childs_count is incorrect';

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

        if( $this->is_empty( $this->id )) {
            $this->error = 'post_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'post_id is incorrect';

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
            $this->childs_count = null;
        }

        return empty( $this->error );
    }

}
