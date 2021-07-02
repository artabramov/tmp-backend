<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserAuth
{
    public function do() {

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate user --
        if(empty($auth_user)) {
            throw new AppException('User auth error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User auth error: user_status is trash.');
        }

        // -- Auth usermeta --
        $auth_user_meta = [];
        foreach($auth_user->user_meta as $meta) {
            $auth_user_meta[$meta->meta_key] = $meta->meta_value;
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $auth_user->id, 
                'create_date' => $auth_user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth_user->user_status,
                'user_token' => $auth_user->user_token,
                'user_email' => $auth_user->user_email,
                'user_name' => $auth_user->user_name,
                'user_meta' => $auth_user_meta
            ]
        ]);
    }
}
