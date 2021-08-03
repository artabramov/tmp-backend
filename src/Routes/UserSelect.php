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

class UserSelect
{
    public function do($user_id) {
    
        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) $user_id;

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        $user->auth_date = Flight::get('date');
        $em->persist($user);
        $em->flush();

        // -- Member ---
        $member = $em->find('\App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('Member error: user_id not found.');
        }

        // -- End --
        if($user->id == $member->id) {

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

        } else {

            // -- End --
            Flight::json([
                'success' => 'true',
                'user' => [
                    'id' => $member->id, 
                    'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                    'auth_date' => $member->auth_date->format('Y-m-d H:i:s'),
                    'user_status' => $member->user_status,
                    'user_name' => $member->user_name
                ]
            ]);
        }

    }
}
