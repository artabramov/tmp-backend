<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserSignout
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

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Update auth --
        $auth->user_token = $auth->create_token();
        Flight::get('em')->persist($auth);
        Flight::get('em')->flush();

        // -- End --
        Flight::json(['success' => 'true']);
    }
}
