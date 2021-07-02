<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserSignin
{
    public function do() {

        // -- Signin auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => Flight::request()->query['user_email'], 'user_hash' => sha1(Flight::request()->query['user_pass'])]);
        $time = new DateTime('now');

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('User signin error: user_email not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User signin error: user_status is trash.');

        } elseif($time->getTimestamp() - $auth_user->remind_date->getTimestamp() > APP_PASS_TIME) {
            throw new AppException('User signin error: user_pass expired.');
        }

        // -- Update auth user --    
        $auth_user->user_status = 'approved';
        $auth_user->user_pass = '';
        $auth_user->user_hash = '';
        Flight::get('em')->persist($auth_user);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $auth_user->id, 
                'create_date' => $auth_user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth_user->user_status,
                'user_token' => $auth_user->user_token,
                'user_email' => $auth_user->user_email,
                'user_name' => $auth_user->user_name
            ]
        ]);
    }
}
