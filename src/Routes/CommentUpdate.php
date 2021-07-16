<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Comment;
use \Doctrine\DBAL\ParameterType;
use \App\Exceptions\AppException;

class CommentUpdate
{
    public function do($comment_id) {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $comment_id = (int) $comment_id;
        $comment_content = (string) Flight::request()->query['comment_content'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($comment_id)) {
            throw new AppException('Initial error: comment_id is empty.');

        } elseif(empty($comment_content)) {
            throw new AppException('Initial error: comment_content is empty.');
        }        

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Comment --
        $comment = Flight::get('em')->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('Comment error: comment_id not found.');

        } elseif($comment->user_id != $auth->id) {
            throw new AppException('Comment error: permission denied.');
        }

        // -- Post --
        $post = Flight::get('em')->find('App\Entities\Post', $comment->post_id);

        if(empty($post)) {
            throw new AppException('Post error: post_id not found.');

        } elseif($post->post_status != 'doing') {
            throw new AppException('Post error: post_status is not doing.');
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

        // -- Update comment --
        $comment->comment_content = $comment_content;
        Flight::get('em')->persist($comment);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
