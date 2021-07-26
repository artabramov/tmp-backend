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

class RoleUpdate
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) Flight::request()->query['user_id'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $role_status = (string) Flight::request()->query['role_status'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Pal --
        $pal = $em->getRepository('\App\Entities\User')->findOneBy(['id' => $user_id]);

        if(empty($pal)) {
            throw new AppException('User error: user_id not found.');

        } elseif($pal->user_status == 'trash') {
            throw new AppException('User error: user_id is trash.');
        }

        // -- Hub --
        $hub = $em->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Hub error: hub_id is trash.');

        } elseif($hub->user_id == $pal->id) {
            throw new AppException('Hub error: permission denied.');
        }

        // -- User role --
        $user_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('User role error: role_status must be admin.');
        }

        // -- Pal role --
        $pal_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $pal->id]);

        if(empty($pal_role)) {
            throw new AppException('User role error: user_role not found.');
        }

        // -- Pal role update --
        $pal_role->role_status = $role_status;
        $em->persist($pal_role);
        $em->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }
}
