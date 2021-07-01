<?php
namespace App\Routes;

class UserUpdate
{
    public function run() {

        // -- Auth --
        $auth = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => (string) \Flight::request()->query['user_token']]);

        if(empty($auth)) {
            \Flight::set('error', 'User update error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            \Flight::set('error', 'User update error: user_status is trash.');

        } else {
            $auth->user_name = (string) \Flight::request()->query['user_name'];
            \Flight::save($auth);
        }

        // -- End --
        \Flight::json();
    }
}
