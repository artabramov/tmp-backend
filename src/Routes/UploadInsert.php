<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Comment, \App\Entities\Upload;
use \Doctrine\DBAL\ParameterType;
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

        // -- Depot --
        $auth_depot = Flight::get('em')->getRepository('\App\Entities\Depot')->findOneBy(['user_id' => $auth->id], ['id' => 'DESC'], 1, 0);
        $depot_size = $auth_depot->expire_date < new \DateTime('now') ? APP_DEPOT_SIZE : (int) $auth_depot->depot_size;
        $uploads_size = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'uploads_size']);

        if((int) $uploads_size->meta_value >= $depot_size) {
            throw new AppException('Upload error: depot size limit exceeded.');
        }

        // -- Make dir --
        $path = APP_UPLOAD_PATH . $auth->id . '/' . date('Y-m-d');
        if(!file_exists($path)) {
            try {
                mkdir($path, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('Upload error: make dir error.');
            }
        }

        // -- Upload files --
        if($files->count() == 0) {
            throw new AppException('Upload error: uploads are empty.');

        } elseif($files->count() > APP_UPLOAD_NUMBER) {
            throw new AppException('Upload error: uploads number limit exceeded.');

        } else {

            // -- Upload --
            foreach($files->keys() as $key) {

                $file = new \Upload\File($key, new \Upload\Storage\FileSystem($path));
                $file->addValidations([new \Upload\Validation\Mimetype(APP_UPLOAD_MIMES), new \Upload\Validation\Size(APP_UPLOAD_FILESIZE)]);
                $file->setName(uniqid());

                $tmp = [
                    'original_name' => $files[$key]['name'],
                    'name' => $path . '/' . $file->getNameWithExtension(),
                    'mime' => $file->getMimetype(),
                    'size' => $file->getSize()
                ];

                try {
                    $file->upload();
                } catch (\Exception $e) {
                    throw new AppException('Upload error: file upload error.');
                }

                $upload = new Upload();
                $upload->user_id = $auth->id;
                $upload->comment_id = $comment->id;
                $upload->upload_name = $tmp['original_name'];
                $upload->upload_file = $tmp['name'];
                $upload->upload_mime = $tmp['mime'];
                $upload->upload_size = $tmp['size'];
                $upload->comment = $comment;
                Flight::get('em')->persist($upload);
                Flight::get('em')->flush();
            }

            /*
            $qb1 = Flight::get('em')->createQueryBuilder();
            $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')->where($qb1->expr()->eq('upload.user_id', $auth->id));
            $qb1_result = $qb1->getQuery()->getResult();

            $uploads_size->meta_value = (int) $qb1_result[0][1];;
            Flight::get('em')->persist($uploads_size);
            Flight::get('em')->flush();
            */

            // -- Recount uploads size --
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

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
