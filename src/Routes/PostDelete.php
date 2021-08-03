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

        $user->auth_date = Flight::get('date');
        $em->persist($user);
        $em->flush();

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

        // -- Post delete --
        Flight::get('em')->remove($post);
        Flight::get('em')->flush();

        // -- Hubmeta cache --
        foreach($hub->hub_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Hubmeta', $meta->id)) {
                $em->getCache()->evictEntity('\App\Entities\Hubmeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
