<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserSelect
{
    public function do($user_id) {
    
        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) $user_id;

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');
        }

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- User ---
        $user = Flight::get('em')->find('\App\Entities\User', $user_id);

        if(empty($user)) {
            throw new AppException('User error: user_id not found.');
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_name' => $user->user_name
            ]
        ]);

    }
}
