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

class HubSelect
{
    public function do($hub_id) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) $hub_id;

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
        }

        // -- Hub meta --
        $hub_meta = [];
        foreach($hub->hub_meta as $meta) {
            $hub_meta[$meta->meta_key] = $meta->meta_value;
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'hub'=> [
                'id' => $hub->id,
                'create_date' => $hub->create_date->format('Y-m-d H:i:s'),
                'hub_status' => $hub->hub_status,
                'hub_name' => $hub->hub_name,
                'hub_meta' => $hub_meta
            ]
        ]);
    }
}
