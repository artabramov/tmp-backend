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

class PostQuery
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $hub_id = (int) Flight::request()->query['hub_id'];
        $post_status = (string) Flight::request()->query['post_status'];
        $post_title = (string) Flight::request()->query['post_title'];
        $post_tag = (string) Flight::request()->query['post_tag'];
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

        } elseif(!in_array($user_role->role_status, ['editor', 'admin'])) {
            throw new AppException('User role error: role_status must be editor or admin.');
        }

        // -- Posts --
        $qb1 = $em->createQueryBuilder();
        $qc1 = $em->createQueryBuilder();
        if(!empty($post_status)) {

            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.hub_id', $hub->id))
                ->andWhere($qb1->expr()->eq('post.post_status', $em->getConnection()->quote($post_status, \Doctrine\DBAL\ParameterType::STRING)))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(POST_QUERY_LIMIT);

            $qc1->select('count(post.id)')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.hub_id', $hub->id))
                ->andWhere($qb1->expr()->eq('post.post_status', $em->getConnection()->quote($post_status, \Doctrine\DBAL\ParameterType::STRING)));

        } elseif(!empty($post_title)) {

            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.hub_id', $hub->id))
                ->andWhere($qb1->expr()->like('post.post_title', '%' . $post_title . '%'))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(POST_QUERY_LIMIT);

            $qc1->select('count(post.id)')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.hub_id', $hub->id))
                ->andWhere($qb1->expr()->like('post.post_title', '%' . $post_title . '%'));

        } elseif(!empty($post_tag)) {

            $qb2 = Flight::get('em')->createQueryBuilder();
            $qb2->select('tag.post_id')
                ->from('App\Entities\Tag', 'tag')
                ->where($qb2->expr()->eq('tag.tag_value', $post_tag));
    
            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.hub_id', $hub->id))
                ->andWhere($qb1->expr()->in('post.id', $qb2->getDQL()))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(POST_QUERY_LIMIT);

            $qc1->select('count(post.id)')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.hub_id', $hub->id))
                ->andWhere($qb1->expr()->in('post.id', $qb2->getDQL()));

        } else {
            throw new AppException('Post error: posts not found.');
        }

        $posts = array_map(fn($n) => $em->find('App\Entities\Post', $n['id']), $qb1->getQuery()->getResult());
        $posts_count = $qc1->getQuery()->getResult();

        // -- End --
        Flight::json([
            'success' => 'true',

            'posts_limit' => POST_QUERY_LIMIT,
            'posts_count' => (int) $posts_count[0][1],

            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'user_name' => $em->find('App\Entities\User', $n->user_id)->user_name,
                'hub_id' => $n->hub_id,
                'hub_name' => $em->find('App\Entities\Hub', $n->hub_id)->hub_name,
                'post_status' => $n->post_status,
                'post_title' => $n->post_title,

                'comments_count' => (int) call_user_func( 
                    function($meta, $key, $default) {
                        $tmp = $meta->filter(function($el) use ($key) {
                            return $el->meta_key == $key;
                        })->first();
                        return empty($tmp) ? $default : $tmp->meta_value;
                    }, $n->post_meta, 'comments_count', 0 )

            ], $posts)
        ]);
    }
}
