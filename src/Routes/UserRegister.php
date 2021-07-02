<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User, \App\Entities\Usermeta, \App\Entities\Hub, \App\Entities\Role;
use \App\Exceptions\AppException;

class UserRegister
{
    public function do() {

        // -- Create auth user --
        $auth_user = new User();
        $auth_user->remind_date = new DateTime('now');
        $auth_user->user_status = 'pending';
        $auth_user->user_token = $auth_user->create_token();
        $auth_user->user_pass = $auth_user->create_pass();
        $auth_user->user_hash = sha1($auth_user->user_pass);
        $auth_user->user_email = Flight::request()->query['user_email'];
        $auth_user->user_name = Flight::request()->query['user_name'];

        // -- Validate auth user --
        if(Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $auth_user->user_email])) {
            throw new AppException('User register error: user_email is occupied.');
        }

        // -- Save auth user --
        Flight::get('em')->persist($auth_user);
        Flight::get('em')->flush();
        
        // -- Create auth user meta --
        $auth_user_meta = new Usermeta();
        $auth_user_meta->user_id = $auth_user->id;
        $auth_user_meta->meta_key = 'user_ip';
        $auth_user_meta->meta_value = Flight::request()->ip;
        $auth_user_meta->user = $auth_user;
        Flight::get('em')->persist($auth_user_meta);
        Flight::get('em')->flush();

        // -- Create hub --
        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $auth_user->id;
        $hub->hub_name = 'My hub';
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- Create auth user role --
        $auth_user_role = new Role();
        $auth_user_role->user_id = $auth_user->id;
        $auth_user_role->hub_id = $hub->id;
        $auth_user_role->role_status = 'admin';
        $auth_user_role->user = $auth_user;
        $auth_user_role->hub = $hub;
        Flight::get('em')->persist($auth_user_role);
        Flight::get('em')->flush();

        // -- Send email --
        Flight::get('phpmailer')->addAddress($auth_user->user_email, $auth_user->user_name);
        Flight::get('phpmailer')->Subject = 'User register';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $auth_user->user_pass . '</i>';
        Flight::get('phpmailer')->send();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' =>  [
                'id' => $auth_user->id, 
                'create_date' => $auth_user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth_user->user_status,
                'user_name' => $auth_user->user_name
            ]
        ]);
    }
}
