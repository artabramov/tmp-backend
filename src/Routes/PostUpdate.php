<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Tag, \App\Entities\Postmeta;
use \App\Exceptions\AppException;

class PostUpdate
{
    public function do($post_id) {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) $post_id;
        $post_status = (string) Flight::request()->query['post_status'];
        $post_title = (string) Flight::request()->query['post_title'];
        $post_tags = explode(',', mb_strtolower((string) Flight::request()->query['post_tags'], 'UTF-8'));
        $post_tags = array_map(fn($value) => trim($value) , $post_tags);
        $post_tags = array_filter($post_tags, fn($value) => !empty($value));
        $post_tags = array_unique($post_tags);

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($post_id)) {
            throw new AppException('Initial error: post_id is empty.');
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

        } elseif($post->user_id != $auth->id) {
            throw new AppException('Post error: permission denied.');
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

        // -- Post --
        $post->post_status = !empty($post_status) ? $post_status : $post->post_status;
        $post->post_title = !empty($post_title) ? $post_title : $post->post_title;
        Flight::get('em')->persist($post);
        Flight::get('em')->flush();

        // TODO:

        /*
        // -- Post tags --
        if(!empty($post_tags)) {
            foreach($post_tags as $post_tag) {
                $tag = new Tag();
                $tag->post_id = $post->id;
                $tag->tag_value = $post_tag;
                $tag->post = $post;
                Flight::get('em')->persist($tag);
                Flight::get('em')->flush();
            }
        }
        */




        // -- Remove old tags --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('tag.id')->from('App\Entities\Tag', 'tag')->where($qb1->expr()->eq('tag.post_id', $post->id));
        $tags_ids = $qb1->getQuery()->getResult();
        $tags = array_map(fn($n) => Flight::get('em')->find('App\Entities\Tag', $n['id']), $tags_ids);

        foreach($tags as $tag) {
            Flight::get('em')->remove($tag);
            Flight::get('em')->flush();
        }

        // -- Insert new tags --
        if(!empty($post_tags)) {
            foreach($post_tags as $post_tag) {
                $tag = new Tag();
                $tag->post_id = $post->id;
                $tag->tag_value = $post_tag;
                $tag->post = $post;
                Flight::get('em')->persist($tag);
                Flight::get('em')->flush();
            }
        }




        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
