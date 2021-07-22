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

        // -- User meta --
        $user_meta = [];
        foreach($user->user_meta as $meta) {
            $user_meta[$meta->meta_key] = $meta->meta_value;
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'update_date' => $user->update_date->format('Y-m-d H:i:s'), 
                'remind_date' => $user->remind_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name,
                'user_meta' => $user_meta
            ]
        ]);
    }
}
