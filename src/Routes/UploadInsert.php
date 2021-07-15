<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Comment, \App\Entities\Upload;
use \App\Exceptions\AppException;

class UploadInsert
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $comment_id = (int) Flight::request()->query['comment_id'];
        $files = Flight::request()->files;

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($comment_id)) {
            throw new AppException('Initial error: comment_id is empty.');

        } elseif($files->count() == 0) {
            throw new AppException('Initial error: files are empty.');
        }        

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }


        // -- Comment --
        $comment = Flight::get('em')->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('Comment error: comment_id not found.');
        }

        // -- Post --
        $post = Flight::get('em')->find('App\Entities\Post', $comment->post_id);

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

        // -- Auth meta (uploads_size) --
        $auth_meta = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'uploads_size']);

        if((int) $auth_meta->meta_value >= APP_UPLOAD_LIMIT) {
            throw new AppException('Limit error: uploads limit exceeded.');
        }

        // == Upload ==
        $path = APP_UPLOAD_PATH . date('Y-m-d');
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $keys = $files->keys();
        $storage = new \Upload\Storage\FileSystem($path);
        $file = new \Upload\File($keys[0], $storage);
        
        // Optionally you can rename the file on upload
        $new_filename = $auth->id . '-' . uniqid();
        $file->setName( $new_filename );
        
        // Validate file upload: http://www.iana.org/assignments/media-types/media-types.xhtml
        $file->addValidations(array(
            new \Upload\Validation\Mimetype(APP_UPLOAD_MIMES),
            new \Upload\Validation\Size(APP_UPLOAD_MAXSIZE)
        ));
        
        
        // Access data about the file that has been uploaded
        $data = array(
            'original_name' => Flight::request()->files[$keys[0]]['name'],
            '_name'      => $file->getName(),
            'name'       => $file->getNameWithExtension(),
            'extension'  => $file->getExtension(),
            'mime'       => $file->getMimetype(),
            'size'       => $file->getSize(),
            'md5'        => $file->getMd5(),
            'dimensions' => $file->getDimensions()
        );
        
        // -- Upload the file --
        try {
            $file->upload();

        } catch (\Exception $e) {
            throw new AppException('Upload error: file upload error.');
        }

        // -- Etc --
        try {

            // -- Upload --
            $upload = new Upload();
            $upload->comment_id = $comment->id;
            $upload->upload_name = $data['original_name'];
            $upload->upload_file = $path . '/' . $data['name'];
            $upload->upload_mime = $data['mime'];
            $upload->upload_size = $data['size'];
            $upload->comment = $comment;
            Flight::get('em')->persist($upload);
            Flight::get('em')->flush();

            // -- Uploads size --
            $auth_meta->meta_value = ((int) $auth_meta->meta_value ) + $upload->upload_size;
            Flight::get('em')->persist($auth_meta);
            Flight::get('em')->flush();

        } catch (\Exception $e) {
            unlink($path . '/' . $data['name']);
        }



        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
