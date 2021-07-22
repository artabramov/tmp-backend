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

        // -- Pal meta --
        $pal_meta = [];
        foreach($pal->user_meta as $meta) {
            $pal_meta[$meta->meta_key] = $meta->meta_value;
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $pal->id, 
                'create_date' => $pal->create_date->format('Y-m-d H:i:s'), 
                'update_date' => $pal->update_date->format('Y-m-d H:i:s'), 
                'user_status' => $pal->user_status,
                'user_name' => $pal->user_name,
                'user_meta' => $pal_meta
            ]
        ]);

    }
}
