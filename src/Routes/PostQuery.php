<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class PostQuery
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $post_status = (string) Flight::request()->query['post_status'];
        $post_title = (string) Flight::request()->query['post_title'];
        $post_tag = (string) Flight::request()->query['post_tag'];
        $offset = (int) Flight::request()->query['offset'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');

        } elseif(empty($post_status) and empty($post_title) and empty($post_tag)) {
            throw new AppException('Initial error: post_status or post_title or post_tag is empty.');
        } 

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Hub --
        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Hub error: hub_id is trash.');
        }

        // -- Auth role --
        $auth_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $auth->id]);

        if(empty($auth_role)) {
            throw new AppException('Auth role error: user_role not found.');

        } elseif(!in_array($auth_role->role_status, ['editor', 'admin'])) {
            throw new AppException('Auth role error: role_status must be editor or admin.');
        }

        // -- Posts --
        $qb1 = Flight::get('em')->createQueryBuilder();
        if(!empty($post_status)) {
            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.post_status', $post_status))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(APP_QUERY_LIMIT);

        } elseif(!empty($post_title)) {


        } elseif(!empty($post_tag)) {

        }

        $tmp = $qb1->getDQL();
        $posts_ids = $qb1->getQuery()->getResult();
        $posts = array_map(fn($n) => Flight::get('em')->find('App\Entities\Post', $n['id']), $posts_ids);

        // -- End --
        Flight::json([
            'success' => 'true',
            'posts_count' => 0,
            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'post_status' => $n->post_status,
                'post_title' => $n->post_name
            ], $hubs)
        ]);
    }
}
