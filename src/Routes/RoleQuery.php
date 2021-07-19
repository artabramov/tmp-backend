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

class RoleQuery
{
    public function do() {

        // -- Vars --

        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $offset = (int) Flight::request()->query['offset'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- Hub --

        $hub = Flight::get('em')->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');

        } elseif($hub->hub_status == 'trash') {
            throw new AppException('Hub error: hub_id is trash.');
        }

        // -- User role --

        $user_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('User role error: role_status must be admin.');
        }

        // -- Roles --

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('role.id')->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.hub_id', $hub_id))
            ->orderBy('role.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(APP_QUERY_LIMIT);

        $roles = array_map(fn($n) => Flight::get('em')->find('App\Entities\Role', $n['id']), $qb1->getQuery()->getResult());

        // -- End --
        
        Flight::json([
            'success' => 'true',
            'roles'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'hub_id' => $n->hub_id,
                'role_status' => $n->role_status,
                'user_name' => Flight::get('em')->find('App\Entities\User', $n->user_id)->user_name,
                'hub_name' => Flight::get('em')->find('App\Entities\Hub', $n->hub_id)->hub_name
            ], $roles)
        ]);
    }
}
