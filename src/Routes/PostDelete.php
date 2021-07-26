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

class PostDelete
{
    public function do($post_id) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) $post_id;

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Post --
        $post = $em->find('App\Entities\Post', $post_id);

        if(empty($post)) {
            throw new AppException('Post error: post_id not found.');

        } elseif($post->user_id != $user->id) {
            throw new AppException('Post error: permission denied.');
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
        $qb2 = $em->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.post_id', $post->id));

        $qb1 = $em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads = array_map(fn($n) => $em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        // -- Files --
        foreach($uploads as $upload) {
            if(file_exists($upload->upload_file)) {
                unlink($upload->upload_file);
            }
        }

        /*
        // -- Delete uploads --
        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($auth->id, ParameterType::INTEGER)));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('upload.id')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads = array_map(fn($n) => Flight::get('em')->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        foreach($uploads as $upload) {

            if(file_exists($upload->upload_file)) {
                unlink($upload->upload_file);
            }

            Flight::get('em')->remove($upload);
            Flight::get('em')->flush();
        }

        // -- Recount uploads size --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')->where($qb1->expr()->eq('upload.user_id', $auth->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $uploads_size = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'uploads_size']);
        $uploads_size->meta_value = (int) $qb1_result[0][1];;
        Flight::get('em')->persist($uploads_size);
        Flight::get('em')->flush();

        // -- Delete comments --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('comment.id')->from('App\Entities\Comment', 'comment')->where($qb1->expr()->eq('comment.post_id', $post->id));
        $comments = array_map(fn($n) => Flight::get('em')->find('App\Entities\Comment', $n['id']), $qb1->getQuery()->getResult());

        foreach($comments as $comment) {
            Flight::get('em')->remove($comment);
            Flight::get('em')->flush();
        }

        // -- Delete tags --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('tag.id')->from('App\Entities\Tag', 'tag')->where($qb1->expr()->eq('tag.post_id', $post->id));
        $tags = array_map(fn($n) => Flight::get('em')->find('App\Entities\Tag', $n['id']), $qb1->getQuery()->getResult());

        foreach($tags as $tag) {
            Flight::get('em')->remove($tag);
            Flight::get('em')->flush();
        }

        // -- Delete post meta --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('postmeta.id')->from('App\Entities\Postmeta', 'postmeta')->where($qb1->expr()->eq('postmeta.post_id', $post->id));
        $metas = array_map(fn($n) => Flight::get('em')->find('App\Entities\Postmeta', $n['id']), $qb1->getQuery()->getResult());

        foreach($metas as $meta) {
            Flight::get('em')->remove($meta);
            Flight::get('em')->flush();
        }
        */

        // -- Post delete --
        Flight::get('em')->remove($post);
        Flight::get('em')->flush();

        // -- Hubmeta cache --
        foreach($hub->hub_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Hubmeta', $meta->id) and $meta->meta_key == 'posts_count') {
                $em->getCache()->evictEntity('\App\Entities\Hubmeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
