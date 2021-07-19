<?php
namespace App\Routes;
use \Flight, 
    \DateTime, 
    \DateInterval,
    \Doctrine\DBAL\ParameterType,
    \App\Exceptions\AppException,
    \App\Entities\User, 
    \App\Entities\Usermeta, 
    \App\Entities\Role, 
    \App\Entities\Vol, 
    \App\Entities\Hub, 
    \App\Entities\Hubmeta, 
    \App\Entities\Post, 
    \App\Entities\Postmeta, 
    \App\Entities\Tag, 
    \App\Entities\Comment, 
    \App\Entities\Upload;

class UserRemind
{
    public function do() {

        // -- Vars --

        $user_email = (string) Flight::request()->query['user_email'];

        if(empty($user_email)) {
            throw new AppException('Initial error: user_email is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);
        $time = new DateTime('now');

        if(empty($user)) {
            throw new AppException('User error: user_email not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_email is trash.');

        } elseif($time->getTimestamp() - $user->remind_date->getTimestamp() < APP_REMIND_TIME) {
            throw new AppException('User error: wait a little bit.');
        }

        // -- Update user --

        $user->remind_date = new DateTime('now');
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        Flight::get('em')->persist($user);
        Flight::get('em')->flush();

        // -- Email --

        Flight::get('phpmailer')->addAddress($user->user_email, $user->user_name);
        Flight::get('phpmailer')->Subject = 'User remind';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $user->user_pass . '</i>';
        Flight::get('phpmailer')->send();

        // -- End --

        Flight::json(['success' => 'true']);
    }
}
