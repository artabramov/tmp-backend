<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class UserQuery
{
    public function do() {

        // -- Initial --
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $offset = (int) Flight::request()->query['offset'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');

        } elseif(empty($hub_id)) {
            throw new AppException('Initial error: hub_id is empty.');
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
        $auth_role = Flight::get('em')->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub->id, 'user_id' => $auth->id]);

        if(empty($auth_role)) {
            throw new AppException('Auth role error: user_role not found.');
        }

        // -- Users --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('role.user_id')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.hub_id', $hub->id));

        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select(['user.id'])
            ->from('App\Entities\User', 'user')
            ->where($qb2->expr()->in('user.id', $qb1->getDQL()))
            ->orderBy('user.user_name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(APP_QUERY_LIMIT);

        $users_ids = $qb2->getQuery()->getResult();
        $users = array_map(fn($n) => Flight::get('em')->find('App\Entities\User', $n['id']), $users_ids);

        // -- End --
        Flight::json([
            'success' => 'true',
            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_status' => $n->user_status,
                'user_name' => $n->user_name
            ], $users)
        ]);
    }
}
