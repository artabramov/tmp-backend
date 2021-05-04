<?php
namespace App\Core;

class Upload extends \App\Core\Echidna
{
    protected $error;
    protected $id;
    protected $create_date;
    protected $update_date;
    protected $user_id;
    protected $post_id;
    protected $upload_status;
    protected $upload_name;
    protected $upload_mime;
    protected $upload_size;
    protected $upload_file;

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
                $this->error = 'upload_id is empty';
                break;

            } elseif( $arg[0] == 'id' and !$this->is_num( $arg[2] )) {
                $this->error = 'upload_id is incorrect';
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

            } elseif( $arg[0] == 'post_id' and $this->is_empty( $arg[2] )) {
                $this->error = 'post_id is empty';
                break;

            } elseif( $arg[0] == 'post_id' and !$this->is_num( $arg[2] )) {
                $this->error = 'post_id is incorrect';
                break;

            } elseif( $arg[0] == 'upload_status' and $this->is_empty( $arg[2] )) {
                $this->error = 'upload_status is empty';
                break;

            } elseif( $arg[0] == 'upload_status' and !$this->is_string( $arg[2], 20 )) {
                $this->error = 'upload_status is incorrect';
                break;

            } elseif( $arg[0] == 'upload_name' and $this->is_empty( $arg[2] )) {
                $this->error = 'upload_name is empty';
                break;

            } elseif( $arg[0] == 'upload_name' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'upload_name is incorrect';
                break;

            } elseif( $arg[0] == 'upload_mime' and $this->is_empty( $arg[2] )) {
                $this->error = 'upload_mime is empty';
                break;

            } elseif( $arg[0] == 'upload_mime' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'upload_mime is incorrect';
                break;

            } elseif( $arg[0] == 'upload_size' and $this->is_empty( $arg[2] )) {
                $this->error = 'upload_size is empty';
                break;

            } elseif( $arg[0] == 'upload_size' and !$this->is_num( $arg[2] )) {
                $this->error = 'upload_size is incorrect';
                break;

            } elseif( $arg[0] == 'upload_file' and $this->is_empty( $arg[2] )) {
                $this->error = 'upload_file is empty';
                break;

            } elseif( $arg[0] == 'upload_file' and !$this->is_string( $arg[2], 255 )) {
                $this->error = 'upload_file is incorrect';
                break;
            }
        }

        if( empty( $this->error )) {
            $rows = $this->select( '*', 'uploads', $args, 1, 0 );

            if ( empty( $rows[0] )) {
                $this->error = 'upload not found';
        
            } else {
                $this->id            = $rows[0]->id;
                $this->create_date   = $rows[0]->create_date;
                $this->update_date   = $rows[0]->update_date;
                $this->user_id       = $rows[0]->user_id;
                $this->post_id       = $rows[0]->post_id;
                $this->upload_status = $rows[0]->upload_status;
                $this->upload_name   = $rows[0]->upload_name;
                $this->upload_mime   = $rows[0]->upload_mime;
                $this->upload_size   = $rows[0]->upload_size;
                $this->upload_file   = $rows[0]->upload_file;
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

        } elseif( !array_key_exists('post_id', $data) or $this->is_empty( $data['post_id'] )) {
            $this->error = 'post_id is empty';
    
        } elseif( !$this->is_num( $data['post_id'] )) {
            $this->error = 'post_id is incorrect';

        } elseif( !array_key_exists('upload_status', $data) or $this->is_empty( $data['upload_status'] )) {
            $this->error = 'upload_status is empty';
    
        } elseif( !$this->is_string( $data['upload_status'], 20 )) {
            $this->error = 'upload_status is incorrect';

        } elseif( !array_key_exists('upload_name', $data) or $this->is_empty( $data['upload_name'] )) {
            $this->error = 'upload_name is empty';
    
        } elseif( !$this->is_string( $data['upload_name'], 255 )) {
            $this->error = 'upload_name is incorrect';

        } elseif( !array_key_exists('upload_mime', $data) or $this->is_empty( $data['upload_mime'] )) {
            $this->error = 'upload_mime is empty';
    
        } elseif( !$this->is_string( $data['upload_mime'], 255 )) {
            $this->error = 'upload_mime is incorrect';

        } elseif( !array_key_exists('upload_size', $data) or $this->is_empty( $data['upload_size'] )) {
            $this->error = 'upload_size is empty';
    
        } elseif( !$this->is_num( $data['upload_size'] )) {
            $this->error = 'upload_size is incorrect';

        } elseif( !array_key_exists('upload_file', $data) or $this->is_empty( $data['upload_file'] )) {
            $this->error = 'upload_file is empty';
    
        } elseif( !$this->is_string( $data['upload_file'], 255 )) {
            $this->error = 'upload_file is incorrect';

        } elseif( $this->is_exists( 'uploads', [['upload_file', '=', $data['upload_file']]] )) {
            $this->error = 'upload_file is occupied';

        } else {
            $this->id = $this->insert( 'uploads', $data );

            if( empty( $this->id )) {
                $this->error = 'upload insert error';

            } else {
                $this->create_date   = $data['create_date'];
                $this->update_date   = $data['update_date'];
                $this->user_id       = $data['user_id'];
                $this->post_id       = $data['post_id'];
                $this->upload_status = $data['upload_status'];
                $this->upload_name   = $data['upload_name'];
                $this->upload_mime   = $data['upload_mime'];
                $this->upload_size   = $data['upload_size'];
                $this->upload_file   = $data['upload_file'];
            }
        }

        return empty( $this->error );
    }

    public function put( array $data ) : bool {

        if( $this->is_empty( $this->id )) {
            $this->error = 'upload_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'upload_id is incorrect';

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

        } elseif( array_key_exists('post_id', $data) and $this->is_empty( $data['post_id'] )) {
            $this->error = 'post_id is empty';

        } elseif( array_key_exists('post_id', $data) and !$this->is_num( $data['post_id'] )) {
            $this->error = 'post_id is incorrect';

        } elseif( array_key_exists('upload_status', $data) and $this->is_empty( $data['upload_status'] )) {
            $this->error = 'upload_status is empty';

        } elseif( array_key_exists('upload_status', $data) and !$this->is_string( $data['upload_status'], 20 )) {
            $this->error = 'upload_status is incorrect';

        } elseif( array_key_exists('upload_name', $data) and $this->is_empty( $data['upload_name'] )) {
            $this->error = 'upload_name is empty';

        } elseif( array_key_exists('upload_name', $data) and !$this->is_string( $data['upload_name'], 255 )) {
            $this->error = 'upload_name incorrect';

        } elseif( array_key_exists('upload_mime', $data) and $this->is_empty( $data['upload_mime'] )) {
            $this->error = 'upload_mime is empty';

        } elseif( array_key_exists('upload_mime', $data) and !$this->is_string( $data['upload_mime'], 255 )) {
            $this->error = 'upload_mime is incorrect';

        } elseif( array_key_exists('upload_size', $data) and $this->is_empty( $data['upload_size'] )) {
            $this->error = 'upload_size is empty';

        } elseif( array_key_exists('upload_size', $data) and !$this->is_num( $data['upload_size'] )) {
            $this->error = 'upload_size is incorrect'; 

        } elseif( array_key_exists('upload_file', $data) and $this->is_empty( $data['upload_file'] )) {
            $this->error = 'upload_file is empty';

        } elseif( array_key_exists('upload_file', $data) and !$this->is_string( $data['upload_file'], 255 )) {
            $this->error = 'upload_file is incorrect';

        } elseif( !$this->update( 'uploads', [['id', '=', $this->id]], $data )) {
            $this->error = 'upload update error';

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
            $this->error = 'upload_id is empty';

        } elseif( !$this->is_num( $this->id )) {
            $this->error = 'upload_id is incorrect';

        } elseif( !$this->delete( 'uploads', [['id', '=', $this->id]] )) {
            $this->error = 'upload delete error';

        } else {
            $this->id            = null;
            $this->create_date   = null;
            $this->update_date   = null;
            $this->user_id       = null;
            $this->post_id       = null;
            $this->upload_status = null;
            $this->upload_name   = null;
            $this->upload_mime   = null;
            $this->upload_size   = null;
            $this->upload_file   = null;
        }

        return empty( $this->error );
    }

}
