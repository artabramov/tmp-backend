<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Comment;
use \App\Exceptions\AppException;

class CommentInsert
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) Flight::request()->query['post_id'];
        $comment_content = (string) Flight::request()->query['comment_content'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($post_id)) {
            throw new AppException('Initial error: post_id is empty.');

        } elseif(empty($comment_content)) {
            throw new AppException('Initial error: comment_content is empty.');
        }        

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Post --
        $post = Flight::get('em')->find('App\Entities\Post', $post_id);

        if(empty($post)) {
            throw new AppException('Post error: post_id not found.');

        } elseif($post->post_status == 'trash') {
            throw new AppException('Post error: post_id is trash.');
        }

        // -- Hub --
        $hub = Flight::get('em')->find('App\Entities\Hub', $post->hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Hub error: hub_id is trash.');
        }

        // -- Auth role --
        $auth_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub->id, 'user_id' => $auth->id]);

        if(empty($auth_role)) {
            throw new AppException('Auth role error: user_role not found.');

        } elseif(!in_array($auth_role->role_status, ['editor', 'admin'])) {
            throw new AppException('Auth role error: role_status must be editor or admin.');
        }

        // -- Comment --
        $comment = new Comment();
        $comment->user_id = $auth->id;
        $comment->post_id = $post->id;
        $comment->comment_content = $comment_content;
        $comment->post = $post;
        Flight::get('em')->persist($comment);
        Flight::get('em')->flush();










        $path = APP_UPLOAD_PATH . date('Y-m-d');
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $keys = Flight::request()->files->keys();
        $storage = new \Upload\Storage\FileSystem( $path );
        $file = new \Upload\File( $keys[0], $storage );
        
        // Optionally you can rename the file on upload
        $new_filename = $auth->id . '-' . uniqid();
        $file->setName( $new_filename );
        
        // Validate file upload
        // MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
        $file->addValidations(array(

            //You can also add multi mimetype validation
            new \Upload\Validation\Mimetype(APP_UPLOAD_MIMES),
        
            // Ensure file is no larger than 5M (use "B", "K", M", or "G")
            new \Upload\Validation\Size(APP_UPLOAD_MAXSIZE)
        ));
        
        // Access data about the file that has been uploaded
        $data = array(
            'original_name' => Flight::request()->files[$keys[0]]['name'],
            '_name'     => $file->getName(),
            'name'       => $file->getNameWithExtension(),
            'extension'  => $file->getExtension(),
            'mime'       => $file->getMimetype(),
            'size'       => $file->getSize(),
            'md5'        => $file->getMd5(),
            'dimensions' => $file->getDimensions()
        );
        
        // Try to upload file
        try {
            $file->upload();

            /*
            $upload = new \App\Entities\Upload;
            Flight::insert( $upload, [
                'user_id' => $user_id,
                'comment_id' => $comment_id,
                'upload_name' => $data['original_name'],
                'upload_mime' => $data['mime'],
                'upload_size' => $data['size'],
                'upload_file' => $path . '/' . $data['name'],
            ]);

            if( !Flight::empty( 'error' )) {
                unlink( $path . '/' . $data['name'] );
            }
            */

        } catch (\Exception $e) {
            /*
            //Flight::set( 'e', $e );
            $errors = $file->getErrors();
            Flight::set( 'error', strtolower($errors[0]) );
            */
        }




        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
