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

        // -- Mate ---
        $mate = Flight::get('em')->find('\App\Entities\User', $user_id);

        if(empty($mate)) {
            throw new AppException('Mate error: user_id not found.');
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $mate->id, 
                'create_date' => $mate->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $mate->user_status,
                'user_name' => $mate->user_name
            ]
        ]);

    }
}
