<?php
namespace App\Routes;
use \Flight,
    \DateTime,
    \DateInterval,
    \App\Exceptions\AppException,
    \App\Entities\Alert,
    \App\Entities\Comment,
    \App\Entities\Hub,
    \App\Entities\Hubmeta,
    \App\Entities\Post,
    \App\Entities\Postmeta,
    \App\Entities\Role,
    \App\Entities\Tag,
    \App\Entities\Upload,
    \App\Entities\User,
    \App\Entities\Usermeta,
    \App\Entities\Vol;

class UserUpdate
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $user_phone = preg_replace('/[^0-9]/', '', (string) Flight::request()->query['user_phone']);
        $user_name = (string) Flight::request()->query['user_name'];

        if(!empty($user_phone) and $em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('User error: user_phone is occupied.');
        }

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        $user->user_phone = $user_phone;
        $user->user_name = $user_name;
        $em->persist($user);
        $em->flush();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }
}
