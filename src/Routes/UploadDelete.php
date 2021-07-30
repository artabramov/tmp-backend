<?php
namespace App\Routes;
use \Flight,
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
    \App\Entities\Vol,
    \App\Exceptions\AppException;

class UploadDelete
{
    public function do($upload_id) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $upload_id = (int) $upload_id;

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Upload --
        $upload = $em->find('\App\Entities\Upload', $upload_id);

        if(empty($upload)) {
            throw new AppException('Upload error: upload not found.');
        }

        // -- Comment --
        $comment = $em->find('App\Entities\Comment', $upload->comment_id);

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

        // -- File --
        if(file_exists($upload->upload_file)) {

            try {
                unlink($upload->upload_file);
                $em->remove($upload);
                $em->flush();

            } catch (\Exception $e) {
                throw new AppException('Upload error: file delete error.');
            }
        }

        // -- Usermeta cache --
        foreach($user->user_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id)) {
                $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
