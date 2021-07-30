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

class HubQuery
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

        // -- Hubs --
        $qb2 = $em->createQueryBuilder();
        $qb2->select('role.hub_id')
            ->from('App\Entities\Role', 'role')
            ->where($qb2->expr()->eq('role.user_id', $user->id));

        $qb1 = $em->createQueryBuilder();
        $qb1->select(['hub.id'])
            ->from('App\Entities\Hub', 'hub')
            ->where($qb1->expr()->in('hub.id', $qb2->getDQL()))
            ->orderBy('hub.hub_name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(HUB_QUERY_LIMIT);

        $hubs = array_map(fn($n) => $em->find('App\Entities\Hub', $n['id']), $qb1->getQuery()->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'hubs_limit' => HUB_QUERY_LIMIT,
            'hubs_count' => (int) call_user_func( 
                function($meta, $key, $default) {
                    $tmp = $meta->filter(function($el) use ($key) {
                        return $el->meta_key == $key;
                    })->first();
                    return empty($tmp) ? $default : $tmp->meta_value;
                }, $user->user_meta, 'roles_count', 0 ),

            'hubs'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'hub_name' => $n->hub_name,
                'user_id' => $n->user_id,
                'user_name' => $em->find('App\Entities\User', $n->user_id)->user_name,

                'roles_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $n->hub_meta, 'roles_count', 0 ),

                'todo_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $n->hub_meta, 'todo_count', 0 ),

                'doing_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $n->hub_meta, 'doing_count', 0 ),

                'done_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $n->hub_meta, 'done_count', 0 ),

            ], $hubs)
        ]);
    }
}
