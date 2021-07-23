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

class RoleInsert
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $user_email = mb_strtolower((string) Flight::request()->query['user_email']);
        $hub_id = (int) Flight::request()->query['hub_id'];
        $role_status = (string) Flight::request()->query['role_status'];

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
        $user_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('User role error: role_status must be admin.');
        }

        // -- Pal --
        $pal = $em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($pal)) {
            throw new AppException('User error: user_id not found.');

        } elseif($pal->user_status == 'trash') {
            throw new AppException('User error: user_id is trash.');
        }

        // -- Pal role --
        $pal_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub->id, 'user_id' => $pal->id]);

        if(!empty($pal_role)) {
            throw new AppException('User role error: role_status is occupied.');
        }

        $pal_role = new Role();
        $pal_role->create_date = Flight::get('date');
        $pal_role->update_date = Flight::get('zero');
        $pal_role->user_id = $pal->id;
        $pal_role->hub_id = $hub->id;
        $pal_role->role_status = $role_status;
        $pal_role->user = $pal;
        $pal_role->hub = $hub;
        $em->persist($pal_role);
        $em->flush();

        // -- Usermeta cache --
        foreach($pal->user_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id) and $meta->meta_key == 'roles_count') {
                $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
            }
        }

        // -- Hubmeta cache --
        foreach($hub->hub_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Hubmeta', $meta->id) and $meta->meta_key == 'roles_count') {
                $em->getCache()->evictEntity('\App\Entities\Hubmeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }
}
