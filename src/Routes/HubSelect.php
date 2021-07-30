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

        // -- End --
        Flight::json([
            'success' => 'true',
            'hub'=> [
                'id' => $hub->id,
                'create_date' => $hub->create_date->format('Y-m-d H:i:s'),
                'hub_name' => $hub->hub_name,
                'user_id' => $n->user_id,
                'user_name' => $em->find('App\Entities\User', $n->user_id)->user_name,

                'roles_count' => call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $hub->hub_meta, 'roles_count', 0 ),

                'posts_count' => call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $hub->hub_meta, 'posts_count', 0 ),

            ]
        ]);
    }
}
