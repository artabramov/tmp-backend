<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserUpdate
{
    public function do() {

        // -- Auth --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate user --
        if(empty($auth_user)) {
            throw new AppException('User update error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User update error: user_status is trash.');
        }
        
        $auth_user->user_name = Flight::request()->query['user_name'];
        Flight::get('em')->persist($auth_user);
        Flight::get('em')->flush();

        // -- End --
        Flight::json(['success' => 'true']);
    }
}
