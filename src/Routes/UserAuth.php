<?php
namespace App\Routes;

class UserAuth
{
    public function run() {

        // -- Auth --
        $auth = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => (string) \Flight::request()->query['user_token']]);

        if(empty($auth)) {
            \Flight::set('error', 'User auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            \Flight::set('error', 'User auth error: user_status is trash.');

        } else {
            $auth_meta = [];
            foreach($auth->user_meta as $meta) {
                $auth_meta[$meta->meta_key] = $meta->meta_value;
            }
        }

        // -- End --
        \Flight::json([ 
            'user' => \Flight::empty('error') ? [
                'id' => $auth->id, 
                'create_date' => $auth->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth->user_status,
                'user_token' => $auth->user_token,
                'user_email' => $auth->user_email,
                'user_name' => $auth->user_name,
                'user_meta' => $auth_meta]
            : [],
        ]);

    }
}
