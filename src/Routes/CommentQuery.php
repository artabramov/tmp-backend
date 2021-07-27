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

class CommentQuery
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) Flight::request()->query['post_id'];
        $offset = (int) Flight::request()->query['offset'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- Post --
        $post = $em->find('App\Entities\Post', $post_id);

        if(empty($post)) {
            throw new AppException('Post error: post_id not found.');
        }

        // -- Hub --
        $hub = $em->find('App\Entities\Hub', $post->id);

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

        // -- Comments --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('comment.id')->from('App\Entities\Comment', 'comment')
            ->where($qb1->expr()->eq('comment.post_id', $post->id))
            ->orderBy('comment.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(COMMENT_QUERY_LIMIT);
        $comments = array_map(fn($n) => $em->find('App\Entities\Comment', $n['id']), $qb1->getQuery()->getResult());

        // -- Comments count --
        $tmp = $post->post_meta->filter(function($element) {
            return $element->meta_key == 'comments_count';
        })->first();
        $comments_count = !empty($tmp->meta_value) ? $tmp->meta_value : 0;

        // -- Delete alerts --
        // TODO...

        // -- End --
        Flight::json([
            'success' => 'true',
            'comments_count' => $comments_count,
            'comments_limit' => COMMENT_QUERY_LIMIT,
            'comments'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'comment_content' => $n->comment_content,
                'comment_uploads' => array_map(fn($m) => [
                    'id' => $m->id,
                    'create_date' => $m->create_date->format('Y-m-d H:i:s')
                ], $n->comment_uploads->toArray())
            ], $comments)
        ]);
    }
}



