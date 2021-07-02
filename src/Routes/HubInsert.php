<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role;
use \App\Exceptions\AppException;

class HubInsert
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

        // -- Hub --
        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $auth_user->id;
        $hub->hub_name = Flight::request()->query['hub_name'];
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- Auth user role --
        $auth_user_role = new Role();
        $auth_user_role->user_id = $auth_user->id;
        $auth_user_role->hub_id = $hub->id;
        $auth_user_role->role_status = 'admin';
        $auth_user_role->user = $auth_user;
        $auth_user_role->hub = $hub;
        Flight::get('em')->persist($auth_user_role);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'hub' => [
                'id' => $hub->id, 
                'create_date' => $hub->create_date->format('Y-m-d H:i:s'), 
                'hub_status' => $hub->hub_status,
                'hub_name' => $hub->hub_name
            ]
        ]);
    }
}
