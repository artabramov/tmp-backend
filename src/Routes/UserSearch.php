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

class UserSearch
{
    public function do($user_search) {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $user_search = (string) $user_search;

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- user search --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $em->createNativeQuery("SELECT id FROM users WHERE (user_email LIKE :user_search OR user_name LIKE :user_search) AND id IN (SELECT to_id FROM vw_users_relations WHERE user_id = :user_id) LIMIT :limit", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('user_search', '%' . $user_search . '%')
            ->setParameter('limit', USER_SEARCH_LIMIT);

        $users = array_map(fn($n) => $em->find('App\Entities\User', $n['id']), $query->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'auth_date' => $n->auth_date->format('Y-m-d H:i:s'),
                'user_email' => $n->user_email,
                'user_name' => $n->user_name
            ], $users)
        ]);
    }
}
