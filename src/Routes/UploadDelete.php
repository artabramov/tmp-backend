<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role, \App\Entities\Post, \App\Entities\Comment, \App\Entities\Upload;
use \Doctrine\DBAL\ParameterType;
use \App\Exceptions\AppException;

class UploadDelete
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $upload_id = (int) Flight::request()->query['upload_id'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($upload_id)) {
            throw new AppException('Initial error: upload_id is empty.');
        }        

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Upload select --
        $upload = Flight::get('em')->find('\App\Entities\Upload', $upload_id);

        if(empty($upload)) {
            throw new AppException('Upload error: upload not found.');
        }

        // -- Comment --
        $comment = Flight::get('em')->find('App\Entities\Comment', $upload->comment_id);

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

        // -- Delete upload --
        if(file_exists($upload->upload_file)) {
            unlink($upload->upload_file);
        }

        Flight::get('em')->remove($upload);
        Flight::get('em')->flush();

        // -- Recount total uploads size --
        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($auth->id, ParameterType::INTEGER)));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $qb1_result = $qb1->getQuery()->getResult();

        $uploads_size = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $auth->id, 'meta_key' => 'uploads_size']);
        $uploads_size->meta_value = (int) $qb1_result[0][1];;
        Flight::get('em')->persist($uploads_size);
        Flight::get('em')->flush();  

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
