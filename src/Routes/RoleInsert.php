<?php
namespace App\Routes;
use \Flight;
use \App\Entities\User, \App\Entities\Hub, \App\Entities\Role;
use \App\Exceptions\AppException;

class RoleInsert
{
    public function do() {

        $user_token = (string) Flight::request()->query['user_token'];
        $user_email = (string) Flight::request()->query['user_email'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $role_status = (string) Flight::request()->query['role_status'];

        // -- Init --
        if(empty($user_token)) {
            throw new AppException('Role insert error: user_token is empty.');

        } elseif(empty($user_email)) {
            throw new AppException('Role insert error: user_email is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Role insert error: hub_id is empty.');

        } elseif(empty($role_status)) {
            throw new AppException('Role insert error: role_status is empty.');
        }        

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('Role insert error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('Role insert error: user_token is trash.');
        }

        // -- Mate user --
        $mate_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        // -- Validate mate user --
        if(empty($mate_user)) {
            throw new AppException('Role insert error: user_email not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('Role insert error: user_email is trash.');
        }

        // -- Select hub --
        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        // -- Validate hub --
        if(empty($hub)) {
            throw new AppException('Role insert error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Role insert error: hub_id is trash.');
        }

        // -- Auth user role --
        $auth_user_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $auth_user->id]);

        // -- Validate auth role --
        if(empty($auth_user_role)) {
            throw new AppException('Role insert error: user_role not found.');

        } elseif($auth_user_role->role_status != 'admin') {
            throw new AppException('Role insert error: role_status must be admin.');
        }

        // -- Mate user role --
        $mate_user_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $mate_user->id]);

        // -- Validate mate role --
        if(!empty($mate_user_role)) {
            throw new AppException('Role insert error: user_role is occupied.');
        }

        // -- Insert mate user role --
        $mate_user_role = new Role();
        $mate_user_role->user_id = $mate_user->id;
        $mate_user_role->hub_id = $hub->id;
        $mate_user_role->role_status = $role_status;
        $mate_user_role->user = $mate_user;
        $mate_user_role->hub = $hub;
        Flight::get('em')->persist($mate_user_role);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
