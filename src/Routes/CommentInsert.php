<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Comment, \App\Entities\Upload;
use \Doctrine\DBAL\ParameterType;
use \App\Exceptions\AppException;

class CommentInsert
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) Flight::request()->query['post_id'];
        $comment_content = (string) Flight::request()->query['comment_content'];
        $files = Flight::request()->files;

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

        } elseif($post->post_status != 'doing') {
            throw new AppException('Post error: post_status is not doing.');
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

        // -- User uploads limit --
        $premium_limit = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'premium_limit']);
        $premium_expire = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'premium_expire']);
        $uploads_limit = new \DateTime($premium_expire->meta_value) < new \DateTime('now') ? APP_UPLOAD_LIMIT : (int) $premium_limit->meta_value;

        // -- Uploads size --
        $uploads_size = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'uploads_size']);

        if((int) $uploads_size->meta_value >= $uploads_limit) {
            throw new AppException('Upload error: uploads size limit exceeded.');
        }

        // -- Comment --
        $comment = new Comment();
        $comment->user_id = $auth->id;
        $comment->post_id = $post->id;
        $comment->comment_content = $comment_content;
        $comment->post = $post;
        Flight::get('em')->persist($comment);
        Flight::get('em')->flush();

        // -- Upload files --
        if($files->count() > APP_UPLOAD_MAXNUMBER) {
            throw new AppException('Upload error: uploads number limit exceeded.');

        } elseif($files->count() > 0) {

            // make uploads path
            $path = APP_UPLOADS_PATH . $auth->id . '/' . date('Y-m-d');

            if(!file_exists(APP_UPLOADS_PATH . $auth->id)) {
                mkdir(APP_UPLOADS_PATH . $auth->id, 0777, true);
            }

            if(!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // upload files
            foreach($files as $key => $file) {

                // file upload
                $file = new \Upload\File($key, new \Upload\Storage\FileSystem($path));
                $file->addValidations([new \Upload\Validation\Mimetype(APP_UPLOADS_MIMES), new \Upload\Validation\Size(APP_UPLOAD_MAXSIZE)]);
                $file->setName(uniqid());

                try {
                    $data = [
                        'original_name' => $files[$key]['name'],
                        'name'          => $path . '/' . $file->getNameWithExtension(),
                        'mime'          => $file->getMimetype(),
                        'size'          => $file->getSize()
                    ];

                    $file->upload();

                    $upload = new Upload();
                    $upload->comment_id = $comment->id;
                    $upload->upload_name = $data['original_name'];
                    $upload->upload_file = $data['name'];
                    $upload->upload_mime = $data['mime'];
                    $upload->upload_size = $data['size'];
                    $upload->comment = $comment;
                    Flight::get('em')->persist($upload);
                    Flight::get('em')->flush();
        
                } catch (\Exception $e) {
                    throw new AppException('Upload error: file upload error.');
                }
            }

            // recount total uploads size
            $qb2 = Flight::get('em')->createQueryBuilder();
            $qb2->select('comment.id')
                ->from('App\Entities\Comment', 'comment')
                ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($auth->id, ParameterType::INTEGER)));
    
            $qb1 = Flight::get('em')->createQueryBuilder();
            $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')
                ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

            $qb1_result = $qb1->getQuery()->getResult();

            $uploads_size->meta_value = (int) $qb1_result[0][1];;
            Flight::get('em')->persist($uploads_size);
            Flight::get('em')->flush();

        }

        






        /*
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

        } catch (\Exception $e) {
            //Flight::set( 'e', $e );
            $errors = $file->getErrors();
            Flight::set( 'error', strtolower($errors[0]) );
        }
        */



        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
