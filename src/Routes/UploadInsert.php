<?php
namespace App\Routes;
use \Flight,
    \DateTime,
    \DateInterval,
    \App\Exceptions\AppException,
    \App\Entities\Alert,
    \App\Entities\Comment,
    \App\Entities\Hub,
    \App\Entities\Hubmeta,
    \App\Entities\Post,
    \App\Entities\Postmeta,
    \App\Entities\Role,
    \App\Entities\Tag,
    \App\Entities\Upload,
    \App\Entities\User,
    \App\Entities\Usermeta,
    \App\Entities\Vol;

class UploadInsert
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $comment_id = (int) Flight::request()->query['comment_id'];
        $files = Flight::request()->files;

        if($files->count() == 0) {
            throw new AppException('Upload error: uploads are empty.');
        }

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Comment --
        $comment = $em->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('Comment error: comment_id not found.');
        }

        // -- Post --
        $post = $em->find('App\Entities\Post', $comment->post_id);

        if(empty($post)) {
            throw new AppException('Post error: post_id not found.');
        }

        // -- Hub --
       $hub = $em->find('App\Entities\Hub', $post->hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');
        }

        // -- User role --
        $user_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');

        } elseif(!in_array($user_role->role_status, ['editor', 'admin'])) {
            throw new AppException('User role error: role_status must be editor or admin.');
        }

        // -- User vol --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('vol.id')
            ->from('App\Entities\Vol', 'vol')
            ->where($qb1->expr()->eq('vol.user_id', $user->id))
            ->andWhere($qb1->expr()->gt('vol.expire_date', ':now'))
            ->setParameter('now', 'NOW()')
            ->orderBy('vol.vol_size', 'DESC')
            ->setMaxResults(1);

        $qb1_result = $qb1->getQuery()->getResult();
        $user_vol = $em->find('App\Entities\Vol', $qb1_result[0]['id']);

        // -- Make dir --
        $path = UPLOAD_PATH . $user->id . '/' . date('Y-m-d');
        if(!file_exists($path)) {
            try {
                mkdir($path, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('Upload error: make dir error.');
            }
        }

        foreach($files->keys() as $key) {

            // -- File --
            $file = new \Upload\File($key, new \Upload\Storage\FileSystem($path));
            $file->addValidations([new \Upload\Validation\Mimetype(UPLOAD_MIMES), new \Upload\Validation\Size(UPLOAD_FILESIZE)]);
            $file->setName(uniqid());

            // -- Uploads sum --
            $user_meta = $em->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $user->id, 'meta_key' => 'uploads_sum']);
            if(!empty($user_meta) and (int) $user_meta->meta_value + (int) $file->getSize() >= $user_vol->vol_size) {
                throw new AppException('Upload error: vol size limit exceeded.');
            }

            // -- Upload --
            $upload = new Upload();
            $upload->create_date = Flight::get('date');
            $upload->update_date = Flight::get('zero');
            $upload->user_id = $user->id;
            $upload->comment_id = $comment->id;
            $upload->upload_name = $files[$key]['name'];
            $upload->upload_file = $path . '/' . $file->getNameWithExtension();
            $upload->upload_mime = $file->getMimetype();
            $upload->upload_size = $file->getSize();
            $upload->post_comment = $comment;

            try {
                $file->upload();
                $em->persist($upload);
                $em->flush();

            } catch (\Exception $e) {
                throw new AppException('Upload error: file upload error.');
            }

            // -- Usermeta cache --
            foreach($user->user_meta->getValues() as $meta) {
                if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id) and $meta->meta_key == 'uploads_sum') {
                    $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
                }
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
