<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserAuth
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');
        } 

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Auth meta --
        $auth_meta = [];
        foreach($auth->user_meta as $meta) {
            $auth_meta[$meta->meta_key] = $meta->meta_value;
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $auth->id, 
                'create_date' => $auth->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth->user_status,
                'user_token' => $auth->user_token,
                'user_email' => $auth->user_email,
                'user_name' => $auth->user_name,
                'user_meta' => $auth_meta
            ]
        ]);
    }
}
