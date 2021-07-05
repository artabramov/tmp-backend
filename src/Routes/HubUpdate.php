<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class HubUpdate
{
    public function do($hub_id) {

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('Hub select error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('Hub select error: user_status is trash.');
        }

        // -- Select hub --
        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        // -- Validate hub --
        if(empty($hub)) {
            throw new AppException('Hub select error: hub_id not found.');
        }

        // -- Check admin user role --
        if(!$hub->users_roles->exists(function($key, $value) use ($auth_user) {return $auth_user->id === $value->user_id and $value->role_status == 'admin';})) {
            throw new AppException('Hub select error: permission denied.');
        }

        // -- Update hub --
        $hub->hub_status = !empty(Flight::request()->query['hub_status']) ? Flight::request()->query['hub_status'] : $hub->hub_status;
        $hub->hub_name = !empty(Flight::request()->query['hub_name']) ? Flight::request()->query['hub_name'] : $hub->hub_name;
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 'success' => 'true' ]);
    }
}
