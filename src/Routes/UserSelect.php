<?php
namespace App\Routes;

class UserSelect
{
    public function run($user_id) {
    
        // select user
        $user = \Flight::get('em')->find('\App\Entities\User', $user_id);

        if(empty($user)) {
            \Flight::set('error', 'User error: id not found.');

        } else {

            $user_meta = [];
            foreach($user->user_meta as $meta) {
                $user_meta[$meta->meta_key] = $meta->meta_value;
            }

            /*
            $user_roles = [];
            foreach($user->user_roles as $role) {
                $user_roles[$role->hub_id] = $role->role_status;
            }
            */
        }

        // Stop
        \Flight::json([ 
            'user' => \Flight::empty('error') ? [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_name' => $user->user_name,
                'user_meta' => $user_meta]
            : [],
        ]);

    }
}
