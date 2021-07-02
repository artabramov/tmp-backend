<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class HubSelect
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

        // TODO: check is user have any role in this hub
        if(!$hub->users_roles->exists(function($key, $element) use ($auth_user) {return $auth_user->id === $element->user_id;})) {
            throw new AppException('Hub select error: permission denied.');
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'hub'=> [
                'id' => $hub->id,
                'create_date' => $hub->create_date->format('Y-m-d H:i:s'),
                'hub_status' => $hub->hub_status,
                'hub_name' => $hub->hub_name,
                'roles' => array_map(fn($m) => [
                    'id' => $m->id,
                    'create_date' => $m->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $m->user_id,
                    'hub_id' => $m->hub_id,
                    'role_status' => $m->role_status,
                ], $hub->users_roles->toArray())
            ]
        ]);
    }
}
