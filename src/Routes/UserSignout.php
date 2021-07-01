<?php
namespace App\Routes;

class UserSignout
{
    public function run() {

        // -- Auth --
        $auth = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => (string) \Flight::request()->query['user_token']]);

        if(empty($auth)) {
            \Flight::set('error', 'User signout error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            \Flight::set('error', 'User signout error: user_status is trash.');

        } else {
            $auth->user_token = $auth->create_token();
            \Flight::save($auth);
        }

        // -- End --
        \Flight::json();
    }
}
