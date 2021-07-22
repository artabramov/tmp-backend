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

class UserRemind
{
    public function do() {

        $em = Flight::get('em');
        $user_email = (string) Flight::request()->query['user_email'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($user)) {
            throw new AppException('User error: user_email not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_email is trash.');

        } elseif(Flight::get('date')->getTimestamp() - $user->remind_date->getTimestamp() < USER_REMIND_TIME) {
            throw new AppException('User error: wait for ' . USER_REMIND_TIME . ' seconds.');
        }

        $user->remind_date = Flight::get('date');
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $em->persist($user);
        $em->flush();

        // -- Email --
        $phpmailer = Flight::get('phpmailer');
        $phpmailer->addAddress($user->user_email, $user->user_name);
        $phpmailer->Subject = 'User remind';
        $phpmailer->Body = 'One-time pass: <i>' . $user->user_pass . '</i>';
        $phpmailer->send();

        // -- End --
        Flight::json([
            'success' => 'true',
            'error' => ''
        ]);
    }
}
