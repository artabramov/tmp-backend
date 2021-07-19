<?php
namespace App\Routes;
use \Flight, 
    \DateTime, 
    \DateInterval,
    \Doctrine\DBAL\ParameterType,
    \App\Exceptions\AppException,
    \App\Entities\User, 
    \App\Entities\Usermeta, 
    \App\Entities\Role, 
    \App\Entities\Vol, 
    \App\Entities\Hub, 
    \App\Entities\Hubmeta,
    \App\Entities\Post, 
    \App\Entities\Postmeta,
    \App\Entities\Tag, 
    \App\Entities\Comment,
    \App\Entities\Upload;

class UserSelect
{
    public function do($user_id) {
    
        // -- Vars --

        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) $user_id;

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');
        }

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- Member ---

        $member = Flight::get('em')->find('\App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('User error: user_id not found.');
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $member->id, 
                'create_date' => $member->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $member->user_status,
                'user_name' => $member->user_name
            ]
        ]);

    }
}
