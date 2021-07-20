<?php
namespace App\Routes;
use \Flight, 
    \DateTime, 
    \DateInterval,
    \Doctrine\DBAL\ParameterType,
    \App\Exceptions\AppException,
    \App\Entities\User, 
    \App\Entities\Usermeta, 
    \App\Entities\Role, 
    \App\Entities\Vol, 
    \App\Entities\Hub, 
    \App\Entities\Hubmeta, 
    \App\Entities\Post, 
    \App\Entities\Postmeta, 
    \App\Entities\Tag, 
    \App\Entities\Comment, 
    \App\Entities\Upload;

class HubDelete
{
    public function do($hub_id) {

        // -- Vars --

        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) $hub_id;

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- Hub --

        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status != 'trash') {
            throw new AppException('Hub error: hub_status must be trash.');

        } elseif($hub->user_id != $user->id) {
            throw new AppException('Hub error: permission denied.');
        }

        // -- Select posts, tags, comments and uploads --

        $qb3 = Flight::get('em')->createQueryBuilder();
        $qb3->select('post.id')
            ->from('App\Entities\Post', 'post')
            ->where($qb3->expr()->eq('post.hub_id', Flight::get('em')->getConnection()->quote($hub->id, ParameterType::INTEGER)));

        $posts = array_map(fn($n) => Flight::get('em')->find('App\Entities\Post', $n['id']), $qb3->getQuery()->getResult());

        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->in('comment.post_id', $qb3->getDQL()));

        $comments = array_map(fn($n) => Flight::get('em')->find('App\Entities\Comment', $n['id']), $qb2->getQuery()->getResult());

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads = array_map(fn($n) => Flight::get('em')->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        // -- Delete uploads --

        foreach($uploads as $upload) {

            if(file_exists($upload->upload_file)) {
                unlink($upload->upload_file);
            }

            Flight::get('em')->remove($upload);
            Flight::get('em')->flush();

            // -- Recount uploads size --

            $qb2 = Flight::get('em')->createQueryBuilder();
            $qb2->select('comment.id')
                ->from('App\Entities\Comment', 'comment')
                ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($upload->user_id, ParameterType::INTEGER)));

            $qb1 = Flight::get('em')->createQueryBuilder();
            $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')
                ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

            $qb1_result = $qb1->getQuery()->getResult();

            $user_meta = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $upload->user_id, 'meta_key' => 'uploads_size']);
            $user_meta->meta_value = (int) $qb1_result[0][1];;
            Flight::get('em')->persist($user_meta);
            Flight::get('em')->flush();
        }

        // -- Delete comments --

        foreach($comments as $comment) {
            Flight::get('em')->remove($comment);
            Flight::get('em')->flush();
        }

        // TODO: DELETE TAGS!
        // delete posts_count
        // ...
        // ...

        // -- Delete posts --

        foreach($posts as $post) {
            Flight::get('em')->remove($post);
            Flight::get('em')->flush();
        }

        // -- Delete roles --

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('role.id')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.hub_id', Flight::get('em')->getConnection()->quote($hub->id, ParameterType::INTEGER)));

        $roles = array_map(fn($n) => Flight::get('em')->find('App\Entities\Role', $n['id']), $qb1->getQuery()->getResult());

        foreach($roles as $role) {
            Flight::get('em')->remove($role);
            Flight::get('em')->flush();

            // -- User meta: roles_count --

            $qb1 = Flight::get('em')->createQueryBuilder();
            $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.user_id', $role->user_id));
            $qb1_result = $qb1->getQuery()->getResult();
    
            $user_meta = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $role->user_id, 'meta_key' => 'roles_count']);
            $user_meta->meta_value = (int) $qb1_result[0][1];
            Flight::get('em')->persist($user_meta);
            Flight::get('em')->flush();

            // -- Hub meta: roles count --

            $qb1 = Flight::get('em')->createQueryBuilder();
            $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.hub_id', $role->hub_id));
            $qb1_result = $qb1->getQuery()->getResult();

            $hub_meta = Flight::get('em')->getRepository('\App\Entities\Hubmeta')->findOneBy(['hub_id' => $role->hub_id, 'meta_key' => 'roles_count']);
            $hub_meta->meta_value = $qb1_result[0][1];
            Flight::get('em')->persist($hub_meta);
            Flight::get('em')->flush();
        }

        // -- Delete hub --

        Flight::get('em')->remove($hub);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 'success' => 'true' ]);
    }
}
