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

        // -- Delete uploads --
        $qb3 = Flight::get('em')->createQueryBuilder();
        $qb3->select('post.id')
            ->from('App\Entities\Post', 'post')
            ->where($qb2->expr()->eq('post.hub_id', Flight::get('em')->getConnection()->quote($hub->id, ParameterType::INTEGER)));

        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb1->expr()->in('comment.post_id', $qb3->getDQL()));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads = array_map(fn($n) => Flight::get('em')->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        foreach($uploads as $upload) {

            if(file_exists($upload->upload_file)) {
                unlink($upload->upload_file);
            }

            Flight::get('em')->remove($upload);
            Flight::get('em')->flush();
        }



        // -- Delete hub --

        /*
        Flight::get('em')->remove($hub);
        Flight::get('em')->flush();
        */

        // -- End --
        Flight::json([ 'success' => 'true' ]);
    }
}
