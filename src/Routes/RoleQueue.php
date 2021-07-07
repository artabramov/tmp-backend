<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class RoleQueue
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) Flight::request()->query['user_id'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $offset = (int) Flight::request()->query['offset'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($user_id) and empty($hub_id)) {
            throw new AppException('Initial error: user_id or hub_id is empty.');
        } 

        // -- Auth --
        $auth = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($auth)) {
            throw new AppException('Auth error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            throw new AppException('Auth error: user_token is trash.');
        }

        // -- Roles --
        $qb1 = Flight::get('em')->createQueryBuilder();
        if(!empty($user_id)) {
            $qb1->select('role.id')->from('App\Entities\Role', 'role')
                ->where($qb1->expr()->eq('role.user_id', $user_id))
                ->orderBy('role.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(APP_SELECT_LIMIT);

        } elseif(!empty($hub_id)) {
            $qb1->select('role.id')->from('App\Entities\Role', 'role')
                ->where($qb1->expr()->eq('role.hub_id', $hub_id))
                ->orderBy('role.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(APP_SELECT_LIMIT);
        }

        $roles_ids = $qb1->getQuery()->getResult();
        $roles = array_map(fn($n) => Flight::get('em')->find('App\Entities\Role', $n['id']), $roles_ids);

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
