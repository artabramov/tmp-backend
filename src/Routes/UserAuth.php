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

class UserAuth
{
    public function do() {
    
        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        $user->auth_date = Flight::get('date');
        $em->persist($user);
        $em->flush();

        // -- End --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'auth_date' => $user->auth_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_phone' => !empty($user->user_phone) ? $user->user_phone : '',
                'user_name' => $user->user_name,

                'roles_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $user->user_meta, 'roles_count', 0 ),

                'relations_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $user->user_meta, 'relations_count', 0 ),

                'uploads_sum' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $user->user_meta, 'uploads_sum', 0 ),

                'alerts_sum' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $user->user_meta, 'alerts_sum', 0 ),

                ]
        ]);
    }
}
