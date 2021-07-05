<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role;
use \App\Exceptions\AppException;

class RoleInsert
{
    public function do() {

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('User select error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User select error: user_status is trash.');
        }

        // -- Auth user role --
        $auth_user_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => '', 'user_id' => '', 'role_status' => '']);

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'role' => [
                'id' => $mate_user_role->id, 
                'create_date' => $mate_user_role->create_date->format('Y-m-d H:i:s'), 
                'user_id' => $mate_user_role->user_id,
                'hub_id' => $mate_user_role->hub_id,
                'role_status' => $mate_user_role->role_status
            ]
        ]);
    }
}
