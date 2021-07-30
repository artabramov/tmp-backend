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

class HubInsert
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_name = (string) Flight::request()->query['hub_name'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Hub --
        $hub = new Hub();
        $hub->create_date = Flight::get('date');
        $hub->update_date = Flight::get('zero');
        $hub->user_id = $user->id;
        $hub->hub_name = $hub_name;
        $em->persist($hub);
        $em->flush();

        // -- User role --
        $user_role = new Role();
        $user_role->create_date = Flight::get('date');
        $user_role->update_date = Flight::get('zero');
        $user_role->user_id = $user->id;
        $user_role->hub_id = $hub->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->hub = $hub;
        $em->persist($user_role);
        $em->flush();

        // -- User meta cache --
        foreach($user->user_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id) and $meta->meta_key == 'roles_count') {
                $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'hub_id' => $hub->id
        ]);
    }
}
