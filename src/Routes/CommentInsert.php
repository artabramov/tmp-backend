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

class CommentInsert
{
    public function do() {

        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $post_id = (int) Flight::request()->query['post_id'];
        $comment_content = (string) Flight::request()->query['comment_content'];
 
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
        $hub = $em->find('App\Entities\Hub', $post->hub_id);

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

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = Flight::get('date');
        $comment->update_date = Flight::get('zero');
        $comment->user_id = $user->id;
        $comment->post_id = $post->id;
        $comment->comment_content = $comment_content;
        $comment->post = $post;
        $em->persist($comment);
        $em->flush();

        // -- Postmeta cache --
        foreach($post->post_meta->getValues() as $meta) {
            if($em->getCache()->containsEntity('\App\Entities\Postmeta', $meta->id)) {
                $em->getCache()->evictEntity('\App\Entities\Postmeta', $meta->id);
            }
        }

        // -- Members --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('role.user_id')
            ->from('App\Entities\Role', 'role')
            ->where($qb1->expr()->eq('role.hub_id', $hub->id));

        $members = array_map(fn($n) => $em->find('App\Entities\User', $n['user_id']), $qb1->getQuery()->getResult());

        // -- Usermeta cache --
        foreach($members as $member) {
            foreach($member->user_meta->getValues() as $meta) {
                if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id)) {
                    $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
                }
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'comment_id' => $comment->id
        ]);
    }
}
