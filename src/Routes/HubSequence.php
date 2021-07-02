<?php
namespace App\Routes;
use \Flight;
//use \App\Entities\Hub as Hub;
use \App\Exceptions\AppException;

class HubSequence
{
    public function do() {

        // -- Auth user --
        $auth_user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => Flight::request()->query['user_token']]);

        // -- Validate auth user --
        if(empty($auth_user)) {
            throw new AppException('User select error: user_token not found.');

        } elseif($auth_user->user_status == 'trash') {
            throw new AppException('User select error: user_status is trash.');
        }

        // -- Select hubs --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('role.hub_id')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.user_id', $auth_user->id));

        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select(['hub.id', 'hub.create_date', 'hub.hub_status', 'hub.hub_name'])
            ->from('App\Entities\Hub', 'hub')
            ->where($qb2->expr()->in('hub.id', $qb1->getDQL()))
            ->orderBy('hub.hub_name', 'ASC')
            ->setFirstResult((int) Flight::request()->query['offset'])
            ->setMaxResults(APP_SELECT_LIMIT);

        $hubs = $qb2->getQuery()->getResult();

        // -- Make correct datetime --
        foreach($hubs as $key=>$hub) {
            $hubs[$key]['create_date'] = $hub['create_date']->format('Y-m-d H:i:s');
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'hubs'=> $hubs
        ]);
    }
}
