<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User;
use \App\Exceptions\AppException;

class UserRegister
{
    public function run() {

        // -- User --
        $user = new User();
        $user->remind_date = new DateTime('now');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_email = Flight::request()->query['user_email'];
        $user->user_name = Flight::request()->query['user_name'];

        if(Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user->user_email])) {
            throw new AppException('User register error: user_email is occupied.');
        } else {
            Flight::save($user);
        }

        /*
        // User meta
        $meta = new \App\Entities\Usermeta();
        $meta->user_id = $user->id;
        $meta->meta_key = 'user_addr';
        $meta->meta_value = (string) \Flight::request()->query['user_addr'];
        $meta->user = $user;
        \Flight::save($meta);

        // Hub
        $hub = new \App\Entities\Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $user->id;
        $hub->hub_name = 'My hub';
        \Flight::save($hub);

        // Role
        $role = new \App\Entities\Role();
        $role->user_id = $user->id;
        $role->hub_id = $hub->id;
        $role->role_status = 'admin';
        $role->user = $user;
        $role->hub = $hub;
        \Flight::save($role);
        */

        /*
        // -- Email --
        \Flight::get('phpmailer')->addAddress($user->user_email, $user->user_name);
        \Flight::get('phpmailer')->Subject = 'User register';
        \Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $user->user_pass . '</i>';
        \Flight::get('phpmailer')->send();
        */

        // -- Stop --
        //\Flight::get('em')->getConnection()->commit();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' =>  [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_name' => $user->user_name]
        ]);
    }
}
