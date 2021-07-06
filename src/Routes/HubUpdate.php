<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class HubUpdate
{
    public function do($hub_id) {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) $hub_id;
        $hub_status = (string) Flight::request()->query['hub_status'];
        $hub_name = (string) Flight::request()->query['hub_name'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');

        } elseif(empty($hub_status)) {
            throw new AppException('Initial error: hub_status is empty.');

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
        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');
        }

        // -- Auth role --
        $auth_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $auth->id]);

        if(empty($auth_role)) {
            throw new AppException('Auth role error: user_role not found.');

        } elseif($auth_role->role_status != 'admin') {
            throw new AppException('Auth role error: user_role must be an admin.');
        }

        // -- Update hub --
        $hub->hub_status = $hub_status;
        $hub->hub_name = $hub_name;
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 'success' => 'true' ]);
    }
}
