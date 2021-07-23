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

class CommentDelete
{
    public function do($comment_id) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $comment_id = (int) $comment_id;

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Comment --
        $comment = $em->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('Comment error: comment_id not found.');

        } elseif($comment->user_id != $user->id) {
            throw new AppException('Comment error: permission denied.');
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

        // -- Uploads --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->eq('upload.comment_id', $comment->id));

        $uploads = array_map(fn($n) => $em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        // -- Files --
        foreach($uploads as $upload) {
            if(file_exists($upload->upload_file)) {
                unlink($upload->upload_file);
            }
        }

        // -- Delete comment --
        $em->remove($comment);
        $em->flush();

        // -- Postmeta cache --
        foreach($post->post_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Postmeta', $meta->id) and $meta->meta_key == 'comments_count') {
                $em->getCache()->evictEntity('\App\Entities\Postmeta', $meta->id);
            }
        }






        /*
        // -- Delete uploads --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('upload.id')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->eq('upload.comment_id', $comment_id));

        $uploads_ids = $qb1->getQuery()->getResult();
        $uploads = array_map(fn($n) => Flight::get('em')->find('App\Entities\Upload', $n['id']), $uploads_ids);

        foreach($uploads as $upload) {

            if(file_exists($upload->upload_file)) {
                unlink($upload->upload_file);
            }

            Flight::get('em')->remove($upload);
            Flight::get('em')->flush();
        }

        // -- Delete comment --
        Flight::get('em')->remove($comment);
        Flight::get('em')->flush();

        // -- Recount total user uploads size --
        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($auth->id, ParameterType::INTEGER)));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $qb1_result = $qb1->getQuery()->getResult();

        $uploads_size = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'uploads_size']);
        $uploads_size->meta_value = (int) $qb1_result[0][1];;
        Flight::get('em')->persist($uploads_size);
        Flight::get('em')->flush();
        */

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
