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

class UserSignin
{
    public function do() {

        // -- Vars --

        $user_email = (string) Flight::request()->query['user_email'];
        $user_pass = (string) Flight::request()->query['user_pass'];

        if(empty($user_email)) {
            throw new AppException('Initial error: user_email is empty.');

        } elseif(empty($user_pass)) {
            throw new AppException('Initial error: user_pass is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email, 'user_hash' => sha1($user_pass)]);
        $time = new DateTime('now');

        if(empty($user)) {
            throw new AppException('User error: user_email not found or user_pass is incorrect.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_status is trash.');

        } elseif($time->getTimestamp() - $user->remind_date->getTimestamp() > APP_PASS_TIME) {
            throw new AppException('User error: user_pass expired.');
        }

        // -- Update user --

        $user->user_status = 'approved';
        $user->user_pass = '';
        $user->user_hash = '';
        Flight::get('em')->persist($user);
        Flight::get('em')->flush();

        // -- End --

        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name
            ]
        ]);
    }
}
