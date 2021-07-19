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

class HubInsert
{
    public function do() {

        // -- Vars --

        $user_token = (string) Flight::request()->query['user_token'];
        $hub_name = (string) Flight::request()->query['hub_name'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_name)) {
            throw new AppException('Initial error: hub_name is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- Hub --

        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $user->id;
        $hub->hub_name = $hub_name;
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- User role --

        $user_role = new Role();
        $user_role->user_id = $user->id;
        $user_role->hub_id = $hub->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->hub = $hub;
        Flight::get('em')->persist($user_role);
        Flight::get('em')->flush();

        // -- Hub meta: roles count --

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.hub_id', $hub->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $hub_meta = new Hubmeta();
        $hub_meta->hub_id = $hub->id;
        $hub_meta->meta_key = 'roles_count';
        $hub_meta->meta_value = $qb1_result[0][1];
        $hub_meta->hub = $hub;
        Flight::get('em')->persist($hub_meta);
        Flight::get('em')->flush();

        // -- User meta: roles_count --

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.user_id', $user->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $user_meta = Flight::get('em')->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $user->id, 'meta_key' => 'roles_count']);
        $user_meta->meta_value = (int) $qb1_result[0][1];
        Flight::get('em')->persist($user_meta);
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
