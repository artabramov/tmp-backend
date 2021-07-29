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

class UserQuery
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $offset = (int) Flight::request()->query['offset'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- User relations --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('to_id', 'to_id');

        $query = $em->createNativeQuery("SELECT to_id FROM vw_users_relations WHERE user_id = :user_id LIMIT :limit OFFSET :offset", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('offset', $offset)
            ->setParameter('limit', USER_QUERY_LIMIT);
        $users = array_map(fn($n) => $em->find('App\Entities\User', $n['to_id']), $query->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'users_limit' => USER_QUERY_LIMIT,
            'users_count' => call_user_func( 
                function($meta, $key, $default) {
                    $tmp = $meta->filter(function($el) use ($key) {
                        return $el->meta_key == $key;
                    })->first();
                    return empty($tmp) ? $default : $tmp->meta_value;
                }, $user->user_meta, 'relations_count', 0 ),

            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_status' => $n->user_status,
                'user_name' => $n->user_name
            ], $users)
        ]);
    }
}
