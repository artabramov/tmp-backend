<?php
namespace App\Routes;

class UserSignin
{
    public function run() {

        // -- Signin --
        $user = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => (string) \Flight::request()->query['user_email'], 'user_hash' => sha1((string) \Flight::request()->query['user_pass'])]);
        $time = new \DateTime('now');

        if(empty($user)) {
            \Flight::set('error', 'User signin error: user_email not found.');

        } elseif($user->user_status == 'trash') {
            \Flight::set('error', 'User signin error: user_status is trash.');

        } elseif($time->getTimestamp() - $user->remind_date->getTimestamp() > APP_PASS_TIME) {
            \Flight::set('error', 'User signin error: user_pass expired.');
            
        } else {
            $user->user_status = 'approved';
            $user->user_pass = '';
            $user->user_hash = '';
            \Flight::save($user);
        }

        // Stop
        \Flight::json([ 
            'user' => \Flight::empty('error') ? [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name]
            : [],
        ]);

    }
}
