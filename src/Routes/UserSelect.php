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

        // -- Pal ---
        $pal = $em->find('\App\Entities\User', $user_id);

        if(empty($pal)) {
            throw new AppException('Pal error: user_id not found.');
        }

        // -- End --
        if($user->id == $pal->id) {

            // -- User meta --
            $user_meta = [];
            foreach($user->user_meta as $meta) {
                $user_meta[$meta->meta_key] = $meta->meta_value;
            }

            // -- User --
            Flight::json([ 
                'user' => [
                    'id' => $user->id, 
                    'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                    'user_status' => $user->user_status,
                    'user_token' => $user->user_token,
                    'user_email' => $user->user_email,
                    'user_phone' => $user->user_phone,
                    'user_name' => $user->user_name,
                    'user_meta' => $user_meta
                ]
            ]);

        } else {

            // -- Pal --
            Flight::json([ 
                'user' => [
                    'id' => $pal->id, 
                    'create_date' => $pal->create_date->format('Y-m-d H:i:s'),
                    'user_status' => $pal->user_status,
                    'user_name' => $pal->user_name
                ]
            ]);
        }

    }
}
