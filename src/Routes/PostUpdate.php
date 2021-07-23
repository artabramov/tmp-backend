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

class PostUpdate
{
    public function do($post_id) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) $post_id;
        $post_status = (string) Flight::request()->query['post_status'];
        $post_title = (string) Flight::request()->query['post_title'];

        $post_tags = explode(',', mb_strtolower((string) Flight::request()->query['post_tags'], 'UTF-8'));
        $post_tags = array_map(fn($value) => trim($value) , $post_tags);
        $post_tags = array_filter($post_tags, fn($value) => !empty($value));
        $post_tags = array_unique($post_tags);

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

        // -- Post --
        $post->post_status = $post_status;
        $post->post_title = $post_title;
        $em->persist($post);
        $em->flush();

        // -- Update tags --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('tag.id')->from('App\Entities\Tag', 'tag')->where($qb1->expr()->eq('tag.post_id', $post->id));
        $tags = array_map(fn($n) => Flight::get('em')->find('App\Entities\Tag', $n['id']), $qb1->getQuery()->getResult());

        foreach($tags as $tag) {
            Flight::get('em')->remove($tag);
            Flight::get('em')->flush();
        }

        foreach($post_tags as $post_tag) {
            $tag = new Tag();
            $tag->create_date = Flight::get('date');
            $tag->update_date = Flight::get('zero');
            $tag->post_id = $post->id;
            $tag->tag_value = $post_tag;
            $tag->post = $post;
            $em->persist($tag);
            $em->flush();
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
