<?php
namespace App\Routes;

class UserPost
{
    public function run() {

        // Create user
        $user = new \App\Entities\User();
        $user->user_email = (string) \Flight::request()->query['user_email'];
        $user->user_name = (string) \Flight::request()->query['user_name'];

        // Save user
        if(\Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user->user_email])) {
            \Flight::set('error', 'User error: email already exists.');
        } else {
            \Flight::save($user);
        }

        // User meta
        $meta = new \App\Entities\Usermeta();
        $meta->user_id = $user->id;
        $meta->meta_key = 'user_addr';
        $meta->meta_value = (string) \Flight::request()->query['user_addr'];
        $meta->user = $user;
        \Flight::save($meta);

        // Hub
        $hub = new \App\Entities\Hub();
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

        // Stop
        \Flight::json([ 
            'user' => \Flight::empty('error') ? [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_name' => $user->user_name ] 
            : [],
        ]);
    }
}
