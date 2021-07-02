<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserSignout
{
    public function do() {

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate user --
        if(empty($auth_user)) {
            throw new AppException('User signout error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User signout error: user_status is trash.');
        }

        // -- Update user --
        $auth_user->user_token = $auth_user->create_token();
        Flight::get('em')->persist($auth_user);
        Flight::get('em')->flush();

        // -- End --
        Flight::json(['success' => 'true']);
    }
}
