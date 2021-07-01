<?php
namespace App\Routes;

class UserSelect
{
    public function run($user_id) {
    
        // -- Auth --
        $auth = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => (string) \Flight::request()->query['user_token']]);

        if(empty($auth)) {
            \Flight::set('error', 'User select error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            \Flight::set('error', 'User select error: user_status is trash.');
        }

        // -- User ---
        $user = \Flight::get('em')->find('\App\Entities\User', $user_id);

        if(empty($user)) {
            \Flight::set('error', 'User select error: user_id not found.');
        }

        // -- End --
        \Flight::json([ 
            'user' => \Flight::empty('error') ? [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_name' => $user->user_name]
            : [],
        ]);

    }
}
