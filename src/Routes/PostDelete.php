<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Tag, \App\Entities\Postmeta, \App\Entities\Upload;
use \Doctrine\DBAL\ParameterType;
use \App\Exceptions\AppException;

class PostDelete
{
    public function do($post_id) {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) $post_id;

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

        // -- Delete uploads --
        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($auth->id, ParameterType::INTEGER)));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('upload.id')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads_ids = $qb1->getQuery()->getResult();
        $uploads = array_map(fn($n) => Flight::get('em')->find('App\Entities\Upload', $n['id']), $uploads_ids);

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

        // -- Post delete --
        Flight::get('em')->remove($post);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
