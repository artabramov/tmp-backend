<?php
namespace App\Routes;
use \Flight;
use \App\Exceptions\AppException;

class HubQueue
{
    public function do() {

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('Hubs query error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('Hubs query error: user_status is trash.');
        }

        // -- Select hubs --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('role.hub_id')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.user_id', $auth_user->id));

        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select(['hub.id'])
            ->from('App\Entities\Hub', 'hub')
            ->where($qb2->expr()->in('hub.id', $qb1->getDQL()))
            ->orderBy('hub.hub_name', 'ASC')
            ->setFirstResult((int) Flight::request()->query['offset'])
            ->setMaxResults(APP_SELECT_LIMIT);

        // -- Get results --
        $hubs_ids = $qb2->getQuery()->getResult();
        $hubs = array_map(fn($n) => Flight::get('em')->find('App\Entities\Hub', $n['id']), $hubs_ids);

        // -- Count total results --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.hub_id)')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.user_id', $auth_user->id));

        $hubs_count = $qb1->getQuery()->getResult();

        // -- End --
        Flight::json([
            'success' => 'true',
            'hubs_count' => $hubs_count[0][1],
            'hubs'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'hub_status' => $n->hub_status,
                'hub_name' => $n->hub_name,
                'posts_count' => 'non-counted',
                'roles_count' => $n->users_roles->count()
            ], $hubs)
        ]);
    }
}
