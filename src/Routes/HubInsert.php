<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role;
use \App\Exceptions\AppException;

class HubInsert
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_name = (string) Flight::request()->query['hub_name'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_name)) {
            throw new AppException('Initial error: hub_name is empty.');
        } 

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Hub --
        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $auth->id;
        $hub->hub_name = $hub_name;
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- Auth role --
        $auth_role = new Role();
        $auth_role->user_id = $auth->id;
        $auth_role->hub_id = $hub->id;
        $auth_role->role_status = 'admin';
        $auth_role->user = $auth;
        $auth_role->hub = $hub;
        Flight::get('em')->persist($auth_role);
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
