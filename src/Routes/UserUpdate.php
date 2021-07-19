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

class UserUpdate
{
    public function do() {

        // -- Vars --

        $user_token = (string) Flight::request()->query['user_token'];
        $user_name = (string) Flight::request()->query['user_name'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($user_name)) {
            throw new AppException('Initial error: user_name is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- Update user --

        $user->update_date = new DateTime('now');
        $user->user_name = $user_name;
        Flight::get('em')->persist($user);
        Flight::get('em')->flush();

        // -- End --

        Flight::json(['success' => 'true']);
    }
}
