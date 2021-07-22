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
        $hub->hub_status = 'custom';
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

        // -- Usermeta cache --
        foreach($user->user_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id)) {
                $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
            }
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'error' => '',
            'hub' => [
                'id' => $hub->id, 
                'create_date' => $hub->create_date->format('Y-m-d H:i:s'), 
                'hub_status' => $hub->hub_status,
                'hub_name' => $hub->hub_name
            ]
        ]);
    }
}
