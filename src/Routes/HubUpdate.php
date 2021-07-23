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

class HubUpdate
{
    public function do($hub_id) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) $hub_id;
        $hub_status = (string) Flight::request()->query['hub_status'];
        $hub_name = (string) Flight::request()->query['hub_name'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Hub --
        $hub = $em->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->user_id != $user->id) {
            throw new AppException('Hub error: permission denied.');
        }

        // -- Update hub --
        $hub->hub_status = $hub_status;
        $hub->hub_name = $hub_name;
        $em->persist($hub);
        $em->flush();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }
}
