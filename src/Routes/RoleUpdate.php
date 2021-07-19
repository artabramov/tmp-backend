<?php
namespace App\Routes;
use \Flight, 
    \DateTime, 
    \DateInterval,
    \Doctrine\DBAL\ParameterType,
    \App\Exceptions\AppException,
    \App\Entities\User, 
    \App\Entities\Usermeta, 
    \App\Entities\Role, 
    \App\Entities\Vol, 
    \App\Entities\Hub, 
    \App\Entities\Hubmeta, 
    \App\Entities\Post, 
    \App\Entities\Postmeta, 
    \App\Entities\Tag, 
    \App\Entities\Comment, 
    \App\Entities\Upload;

class RoleUpdate
{
    public function do() {

        // -- Vars --

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

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- Member --

        $member = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['id' => $user_id]);

        if(empty($member)) {
            throw new AppException('User error: user_id not found.');

        } elseif($member->user_status == 'trash') {
            throw new AppException('User error: user_id is trash.');
        }

        // -- Hub --

        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Hub error: hub_id is trash.');

        } elseif($hub->user_id == $member->id) {
            throw new AppException('Hub error: permission denied.');
        }

        // -- User role --

        $user_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('User role error: role_status must be admin.');
        }

        // -- Member role --

        $member_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $member->id]);

        if(empty($member_role)) {
            throw new AppException('User role error: user_role not found.');
        }

        // -- Member role update --

        $member_role->role_status = $role_status;
        Flight::get('em')->persist($member_role);
        Flight::get('em')->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true',
        ]);
    }
}
