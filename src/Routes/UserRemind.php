<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserRemind
{
    public function do() {

        // -- Initial --
        $user_email = (string) Flight::request()->query['user_email'];

        if(empty($user_email)) {
            throw new AppException('Initial error: user_email is empty.');
        } 

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);
        $time = new DateTime('now');

        if(empty($auth)) {
            throw new AppException('Auth error: user_email not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_email is trash.');

        } elseif($time->getTimestamp() - $auth->remind_date->getTimestamp() < APP_REMIND_TIME) {
            throw new AppException('Auth error: wait a little bit.');
        }

        // -- Update auth --
        $auth->remind_date = new DateTime('now');
        $auth->user_pass = $auth->create_pass();
        $auth->user_hash = sha1($auth->user_pass);
        Flight::get('em')->persist($auth);
        Flight::get('em')->flush();

        // -- Email --
        Flight::get('phpmailer')->addAddress($auth->user_email, $auth->user_name);
        Flight::get('phpmailer')->Subject = 'User remind';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $auth->user_pass . '</i>';
        Flight::get('phpmailer')->send();

        // -- End --
        Flight::json(['success' => 'true']);
    }
}
