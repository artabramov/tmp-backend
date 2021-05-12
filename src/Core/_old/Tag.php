<?php
namespace App\Core;

class Tag extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $create_date;
    protected $update_date;
    protected $post_id;
    protected $tag_key;
    protected $tag_value;

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
                $this->error = 'tag_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'tag_id is incorrect';
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

            } elseif( $arg[0] == 'post_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'post_id is empty';
                break;

            } elseif( $arg[0] == 'post_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'post_id is incorrect';
                break;

            } elseif( $arg[0] == 'tag_key' and $this->is_empty( $arg[2] )) {
                $this->error = 'tag_key is empty';
                break;

            } elseif( $arg[0] == 'tag_key' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'tag_key is incorrect';
                break;

            } elseif( $arg[0] == 'tag_value' and $this->is_empty( $arg[2] )) {
                $this->error = 'tag_value is empty';
                break;

            } elseif( $arg[0] == 'tag_value' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'tag_value is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'tags', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'tag not found';
        
            } else {
                $this->id          = $rows[0]->id;
                $this->create_date = $rows[0]->create_date;
                $this->update_date = $rows[0]->update_date;
                $this->post_id     = $rows[0]->post_id;
                $this->tag_key     = $rows[0]->tag_key;
                $this->tag_value   = $rows[0]->tag_value;
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

        } elseif( !array_key_exists('post_id', $data) or $this->is_empty( $data['post_id'] )) {
            $this->error = 'post_id is empty';
    
        } elseif( !$this->is_num( $data['post_id'] )) {
            $this->error = 'post_id is incorrect';

        } elseif( !array_key_exists('tag_key', $data) or $this->is_empty( $data['tag_key'] )) {
            $this->error = 'tag_key is empty';
    
        } elseif( !$this->is_string( $data['tag_key'], 20 )) {
            $this->error = 'tag_key is incorrect';

        } elseif( !array_key_exists('tag_value', $data) or $this->is_empty( $data['tag_value'] )) {
            $this->error = 'tag_value is empty';
    
        } elseif( !$this->is_string( $data['tag_value'], 255 )) {
            $this->error = 'tag_value is incorrect';

        } else {
            $this->id = $this->insert( 'tags', $data );

            if( empty( $this->id )) {
                $this->error = 'tag insert error';

            } else {
                $this->create_date = $data['create_date'];
                $this->update_date = $data['update_date'];
                $this->post_id     = $data['post_id'];
                $this->tag_key     = $data['tag_key'];
                $this->tag_value   = $data['tag_value'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'tag_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'tag_id is incorrect';

        } elseif( array_key_exists('create_date', $data) and $this->is_empty( $data['create_date'] )) {
            $this->error = 'create_date is empty';

        } elseif( array_key_exists('create_date', $data) and !$this->is_datetime( $data['create_date'] )) {
            $this->error = 'create_date is incorrect';

        } elseif( array_key_exists('update_date', $data) and $this->is_empty( $data['update_date'] )) {
            $this->error = 'update_date is empty';

        } elseif( array_key_exists('update_date', $data) and !$this->is_datetime( $data['update_date'] )) {
            $this->error = 'update_date is incorrect';

        } elseif( array_key_exists('post_id', $data) and $this->is_empty( $data['post_id'] )) {
            $this->error = 'post_id is empty';

        } elseif( array_key_exists('post_id', $data) and !$this->is_num( $data['post_id'] )) {
            $this->error = 'post_id is incorrect';

        } elseif( array_key_exists('tag_key', $data) and $this->is_empty( $data['tag_key'] )) {
            $this->error = 'tag_key is empty';

        } elseif( array_key_exists('tag_key', $data) and !$this->is_string( $data['tag_key'], 20 )) {
            $this->error = 'tag_key is incorrect';

        } elseif( array_key_exists('tag_value', $data) and $this->is_empty( $data['tag_value'] )) {
            $this->error = 'tag_value is empty';

        } elseif( array_key_exists('tag_value', $data) and !$this->is_string( $data['tag_value'], 255 )) {
            $this->error = 'tag_value is incorrect';

        } elseif( !$this->update( 'tags', [['id', '=', $this->id]], $data )) {
            $this->error = 'tag update error';

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
            $this->error = 'tag_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'tag_id is incorrect';

        } elseif( !$this->delete( 'tags', [['id', '=', $this->id]] )) {
            $this->error = 'tag delete error';

        } else {
            $this->id          = null;
            $this->create_date = null;
            $this->update_date = null;
            $this->post_id     = null;
            $this->tag_key     = null;
            $this->tag_value   = null;
        }

        return empty( $this->error );
    }

}
