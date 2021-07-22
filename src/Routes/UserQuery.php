<?php
namespace App\Routes;
use \Flight,
    \DateTime,
    \DateInterval,
    \App\Exceptions\AppException,
    \App\Entities\Alert,
    \App\Entities\Comment,
    \App\Entities\Hub,
    \App\Entities\Hubmeta,
    \App\Entities\Post,
    \App\Entities\Postmeta,
    \App\Entities\Role,
    \App\Entities\Tag,
    \App\Entities\Upload,
    \App\Entities\User,
    \App\Entities\Usermeta,
    \App\Entities\Vol;

class UserQuery
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $offset = (int) Flight::request()->query['offset'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Hub --
        $hub = $em->find('App\Entities\Hub', $hub_id);

        if(empty($hub)) {
            throw new AppException('Hub error: hub_id not found.');
        }

        // -- User role --
        $user_role = $em->getRepository('\App\Entities\Role')->findOneBy(['hub_id' => $hub->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('User role error: user_role not found.');
        }

        // -- Users --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('role.user_id')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.hub_id', $hub->id));

        $qb2 = $em->createQueryBuilder();
        $qb2->select(['user.id'])
            ->from('App\Entities\User', 'user')
            ->where($qb2->expr()->in('user.id', $qb1->getDQL()))
            ->orderBy('user.user_name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(USER_QUERY_LIMIT);

        $users = array_map(fn($n) => $em->find('App\Entities\User', $n['id']), $qb2->getQuery()->getResult());

        // -- End --
        Flight::json([
            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_status' => $n->user_status,
                'user_name' => $n->user_name
            ], $users)
        ]);
    }
}
