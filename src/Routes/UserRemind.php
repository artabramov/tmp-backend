<?php
namespace App\Routes;

class UserRemind
{
    public function run() {

        // Get user
        $user = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => (string) \Flight::request()->query['user_email']]);
        $time = new \DateTime('now');

        if(empty($user)) {
            \Flight::set('error', 'User remind error: user_email not found.');

        } elseif($user->user_status == 'trash') {
            \Flight::set('error', 'User remind error: user_status is trash.');

        } elseif($time->getTimestamp() - $user->remind_date->getTimestamp() < APP_REMIND_TIME) {
            \Flight::set('error', 'User remind error: wait a little bit.');

        } else {
            $user->remind_date = new \DateTime('now');
            $user->user_pass = $user->create_pass();
            $user->user_hash = sha1($user->user_pass);
            \Flight::save($user);
        }

        // send email
        \Flight::email($user->user_email, 'User', 'User remind', 'One-time pass: <i>' . $user->user_pass . '</i>');

        // Stop
        \Flight::json();

    }
}
