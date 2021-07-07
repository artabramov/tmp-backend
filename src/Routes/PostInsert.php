<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Tag;
use \App\Exceptions\AppException;

class PostInsert
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $post_status = (string) Flight::request()->query['post_status'];
        $post_title = (string) Flight::request()->query['post_title'];

        $post_tags = explode( ',', mb_strtolower( (string) Flight::request()->query['post_tags'], 'UTF-8' ));
        $post_tags = array_map( fn( $value ) => trim( $value ) , $post_tags );
        $post_tags = array_unique( $post_tags );

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');

        } elseif(empty($post_status)) {
            throw new AppException('Initial error: post_status is empty.');

        } elseif(empty($post_title)) {
            throw new AppException('Initial error: post_title is empty.');
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

        // TODO: insert post_meta and post_tags
        //...

        $post = new Post();
        $post->user_id = $auth->id;
        $post->hub_id = $hub->id;
        $post->post_status = $post_status;
        $post->post_title = $post_title;
        $post->post_meta = $user;
        $post->post_tags = $hub;
        Flight::get('em')->persist($user_role);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
