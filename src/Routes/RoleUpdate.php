<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role;
use \App\Exceptions\AppException;

class RoleUpdate
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) Flight::request()->query['user_id'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $role_status = (string) Flight::request()->query['role_status'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($user_id)) {
            throw new AppException('Initial error: user_id is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');

        } elseif(empty($role_status)) {
            throw new AppException('Initial error: role_status is empty.');
        }        

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Mate --
        $mate = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['id' => $user_id]);

        if(empty($mate)) {
            throw new AppException('Mate error: user_id not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Mate error: user_id is trash.');
        }

        // -- Hub --
        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Hub error: hub_id is trash.');
        }

        // -- Auth role --
        $auth_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $auth->id]);

        if(empty($auth_role)) {
            throw new AppException('Auth role error: user_role not found.');

        } elseif($auth_role->role_status != 'admin') {
            throw new AppException('Auth role error: role_status must be admin.');
        }

        // -- Mate role --
        $mate_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $mate->id]);

        if(empty($mate_role)) {
            throw new AppException('Mate role error: user_role not found.');
        }

        // -- Mate role update --
        $mate_role->role_status = $role_status;
        Flight::get('em')->persist($mate_role);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
