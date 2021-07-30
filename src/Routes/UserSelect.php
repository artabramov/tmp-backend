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

        // -- Member ---
        $member = $em->find('\App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('Member error: user_id not found.');
        }

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
