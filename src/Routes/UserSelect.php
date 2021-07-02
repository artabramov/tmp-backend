<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserSelect
{
    public function do($user_id) {
    
        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('User select error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User select error: user_status is trash.');
        }

        // -- Select mate user ---
        $mate_user = Flight::get('em')->find('\App\Entities\User', $user_id);

        // -- Validate mate user --
        if(empty($mate_user)) {
            throw new AppException('User select error: user_id not found.');
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $mate_user->id, 
                'create_date' => $mate_user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $mate_user->user_status,
                'user_name' => $mate_user->user_name
            ]
        ]);

    }
}
