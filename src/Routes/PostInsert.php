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

class PostInsert
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
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

        // -- Hub --
        $hub = $em->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');
        }

        // -- User role --
        $user_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');

        } elseif(!in_array($user_role->role_status, ['editor', 'admin'])) {
            throw new AppException('User role error: role_status must be editor or admin.');
        }

        // -- Post --
        $post = new Post();
        $post->create_date = Flight::get('date');
        $post->update_date = Flight::get('zero');
        $post->user_id = $user->id;
        $post->hub_id = $hub->id;
        $post->post_status = $post_status;
        $post->post_title = $post_title;
        $em->persist($post);
        $em->flush();

        // -- Tags --
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

        // -- Hubmeta cache --
        foreach($hub->hub_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Hubmeta', $meta->id) and $meta->meta_key == 'posts_count') {
                $em->getCache()->evictEntity('\App\Entities\Hubmeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'error' => ''
        ]);
    }
}
