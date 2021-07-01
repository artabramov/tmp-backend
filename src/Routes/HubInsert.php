<?php
namespace App\Routes;

class HubInsert
{
    public function run() {

        // -- Auth --
        $auth = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => (string) \Flight::request()->query['user_token']]);

        if(empty($auth)) {
            \Flight::set('error', 'Hub insert error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            \Flight::set('error', 'Hub insert error: user_status is trash.');
        }

        // -- Hub --
        if(empty(\Flight::get('error'))) {
            $hub = new \App\Entities\Hub();
            $hub->hub_status = 'custom';
            $hub->user_id = $auth->id;
            $hub->hub_name = (string) \Flight::request()->query['hub_name'];
            \Flight::save($hub);
        }

        // -- Role --
        if(empty(\Flight::get('error'))) {
            $role = new \App\Entities\Role();
            $role->user_id = $auth->id;
            $role->hub_id = $hub->id;
            $role->role_status = 'admin';
            $role->user = $auth;
            $role->hub = $hub;
            \Flight::save($role);
        }

        // -- End --
        \Flight::json();
    }
}
