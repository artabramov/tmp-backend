<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserRemind
{
    public function do() {

        // -- Select auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => Flight::request()->query['user_email']]);
        $time = new DateTime('now');

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('User remind error: user_email not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User remind error: user_status is trash.');

        } elseif($time->getTimestamp() - $auth_user->remind_date->getTimestamp() < APP_REMIND_TIME) {
            throw new AppException('User remind error: wait a little bit.');
        }

        // -- Update auth user --
        $auth_user->remind_date = new DateTime('now');
        $auth_user->user_pass = $auth_user->create_pass();
        $auth_user->user_hash = sha1($auth_user->user_pass);
        Flight::get('em')->persist($auth_user);
        Flight::get('em')->flush();

        // -- Send email --
        Flight::get('phpmailer')->addAddress($auth_user->user_email, $auth_user->user_name);
        Flight::get('phpmailer')->Subject = 'User remind';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $auth_user->user_pass . '</i>';
        Flight::get('phpmailer')->send();

        // -- End --
        Flight::json(['success' => 'true']);
    }
}
