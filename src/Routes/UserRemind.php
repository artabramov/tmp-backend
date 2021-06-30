<?php
namespace App\Routes;

class UserRemind
{
    public function run() {

        // Get user
        $user = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => (string) \Flight::request()->query['user_email']]);
        $time = new \DateTime('now', new \DateTimeZone(APP_TIMEZONE));




        $current_datetime = new \DateTime('now', new \DateTimeZone(APP_TIMEZONE));
        $remind_datetime = $user->remind_date;
        //$remind_datetime->setTimezone(new \DateTimeZone(APP_TIMEZONE));

        $current_timestamp = $current_datetime->getTimestamp();
        $remind_timestamp = $remind_datetime->getTimestamp();
        $diff = $current_timestamp - $remind_timestamp;



        if(empty($user)) {
            \Flight::set('error', 'User error: email not found.');

        } elseif($user->user_status == 'trash') {
            \Flight::set('error', 'User error: status is trash.');

        } elseif($time->getTimestamp() - $user->remind_date->getTimestamp() < APP_REMIND_TIME) {
            \Flight::set('error', 'User error: wait a bit.');

        } else {
            //$user->remind_date = new \DateTime('now', new \DateTimeZone(APP_TIMEZONE));
            $user->user_pass = $user->create_pass();
            $user->user_hash = sha1($user->user_pass);
            \Flight::save($user);
        }






        // Stop
        \Flight::json();

    }
}
