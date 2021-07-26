<?php
namespace App\Routes;
use \Flight,
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
    \App\Entities\Vol,
    \App\Exceptions\AppException;

class UserSignin
{
    public function do() {

        $em = Flight::get('em');
        $user_email = (string) Flight::request()->query['user_email'];
        $user_pass = (string) Flight::request()->query['user_pass'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email, 'user_hash' => sha1($user_pass)]);

        if(empty($user)) {
            throw new AppException('User error: user_email or user_pass is incorrect.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_status is trash.');

        } elseif(Flight::get('date')->getTimestamp() - $user->remind_date->getTimestamp() > USER_PASS_TIME) {
            throw new AppException('User error: user_pass expired.');
        }

        $user->user_status = 'approved';
        $user->user_pass = null;
        $user->user_hash = null;
        $em->persist($user);
        $em->flush();

        // -- End --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_phone' => $user->user_phone,
                'user_name' => $user->user_name
            ]
        ]);
    }
}
