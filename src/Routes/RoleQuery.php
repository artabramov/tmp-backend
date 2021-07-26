<?php
namespace App\Routes;
use \Flight,
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
    \App\Entities\Vol,
    \App\Exceptions\AppException;

class RoleQuery
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

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('User role error: role_status must be admin.');
        }

        // -- Roles --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('role.id')->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.hub_id', $hub->id))
            ->orderBy('role.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(ROLE_QUERY_LIMIT);

        $roles = array_map(fn($n) => $em->find('App\Entities\Role', $n['id']), $qb1->getQuery()->getResult());

        // -- Hub meta --
        $roles_count = 0;
        foreach($hub->hub_meta as $meta) {
            if($meta->meta_key == 'roles_count') {
                $roles_count = $meta->meta_value;
                break;
            }
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'roles_count' => $roles_count,
            'roles'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'hub_id' => $n->hub_id,
                'role_status' => $n->role_status,
                'user_name' => $em->find('App\Entities\User', $n->user_id)->user_name,
                'hub_name' => $em->find('App\Entities\Hub', $n->hub_id)->hub_name
            ], $roles)
        ]);
    }
}
