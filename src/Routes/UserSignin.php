<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserSignin
{
    public function do() {

        // -- Initial --
        $user_email = (string) Flight::request()->query['user_email'];
        $user_pass = (string) Flight::request()->query['user_pass'];

        if(empty($user_email)) {
            throw new AppException('Initial error: user_email is empty.');

        } elseif(empty($user_pass)) {
            throw new AppException('Initial error: user_pass is empty.');
        } 

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email, 'user_hash' => sha1($user_pass)]);
        $time = new DateTime('now');

        if(empty($auth)) {
            throw new AppException('Auth error: user_email not found or user_pass is incorrect.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_status is trash.');

        } elseif($time->getTimestamp() - $auth->remind_date->getTimestamp() > APP_PASS_TIME) {
            throw new AppException('Auth error: user_pass expired.');
        }

        // -- Update auth user --    
        $auth->user_status = 'approved';
        $auth->user_pass = '';
        $auth->user_hash = '';
        Flight::get('em')->persist($auth);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $auth->id, 
                'create_date' => $auth->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth->user_status,
                'user_token' => $auth->user_token,
                'user_email' => $auth->user_email,
                'user_name' => $auth->user_name
            ]
        ]);
    }
}
