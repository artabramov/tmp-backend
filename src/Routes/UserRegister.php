<?php
namespace App\Routes;
use \Flight, \DateTime, \DateInterval;
use \App\Entities\User, \App\Entities\Usermeta, \App\Entities\Role, \App\Entities\Depot, \App\Entities\Hub, \App\Entities\Hubmeta, \App\Entities\Post, \App\Entities\Postmeta, \App\Entities\Tag, \App\Entities\Comment, \App\Entities\Upload;
use \Doctrine\DBAL\ParameterType;
use \App\Exceptions\AppException;

class UserRegister
{
    public function do() {

        // -- Initial --
        $user_email = (string) Flight::request()->query['user_email'];
        $user_name = (string) Flight::request()->query['user_name'];

        if(empty($user_email)) {
            throw new AppException('Initial error: user_email is empty.');

        } elseif(empty($user_name)) {
            throw new AppException('Initial error: user_name is empty.');
        }  

        // -- User --
        if(Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('Auth error: user_email is occupied.');
        }

        $auth = new User();
        $auth->remind_date = new DateTime('now');
        $auth->user_status = 'pending';
        $auth->user_token = $auth->create_token();
        $auth->user_pass = $auth->create_pass();
        $auth->user_hash = sha1($auth->user_pass);
        $auth->user_email = $user_email;
        $auth->user_name = $user_name;
        Flight::get('em')->persist($auth);
        Flight::get('em')->flush();

        // -- Depot --
        $depot = new Depot();
        $depot->user_id = $auth->id;
        $depot->depot_size = APP_DEPOT_SIZE;
        Flight::get('em')->persist($depot);
        Flight::get('em')->flush();

        // -- Hub --
        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $auth->id;
        $hub->hub_name = 'My hub';
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- Role --
        $auth_role = new Role();
        $auth_role->user_id = $auth->id;
        $auth_role->hub_id = $hub->id;
        $auth_role->role_status = 'admin';
        $auth_role->user = $auth;
        $auth_role->hub = $hub;
        Flight::get('em')->persist($auth_role);
        Flight::get('em')->flush();

        // -- Post --
        $post = new Post();
        $post->user_id = $auth->id;
        $post->hub_id = $hub->id;
        $post->post_status = 'todo';
        $post->post_title = 'Hello, world!';
        Flight::get('em')->persist($post);
        Flight::get('em')->flush();

        // -- Comment --
        $comment = new Comment();
        $comment->user_id = $auth->id;
        $comment->post_id = $post->id;
        $comment->comment_content = 'Just a comment.';
        $comment->post = $post;
        Flight::get('em')->persist($comment);
        Flight::get('em')->flush();

        // -- Post meta: comments count --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(comment.id)')->from('App\Entities\Comment', 'comment')->where($qb1->expr()->eq('comment.post_id', $post->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $post_meta = new Postmeta();
        $post_meta->post_id = $post->id;
        $post_meta->meta_key = 'comments_count';
        $post_meta->meta_value = (int) $qb1_result[0][1];
        $post_meta->post = $post;
        Flight::get('em')->persist($post_meta);
        Flight::get('em')->flush();

        // -- Hub meta: posts count --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(post.id)')->from('App\Entities\Post', 'post')->where($qb1->expr()->eq('post.hub_id', $hub->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $hub_meta = new Hubmeta();
        $hub_meta->hub_id = $hub->id;
        $hub_meta->meta_key = 'posts_count';
        $hub_meta->meta_value = $qb1_result[0][1];
        $hub_meta->hub = $hub;
        Flight::get('em')->persist($hub_meta);
        Flight::get('em')->flush();

        // -- Hub meta: roles count --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.hub_id', $hub->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $hub_meta = new Hubmeta();
        $hub_meta->hub_id = $hub->id;
        $hub_meta->meta_key = 'roles_count';
        $hub_meta->meta_value = $qb1_result[0][1];
        $hub_meta->hub = $hub;
        Flight::get('em')->persist($hub_meta);
        Flight::get('em')->flush();

        // -- User meta: roles_count --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.user_id', $auth->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $auth_meta = new Usermeta();
        $auth_meta->user_id = $auth->id;
        $auth_meta->meta_key = 'roles_count';
        $auth_meta->meta_value = (int) $qb1_result[0][1];
        $auth_meta->user = $auth;
        Flight::get('em')->persist($auth_meta);
        Flight::get('em')->flush();

        // -- User meta: uploads size --
        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($auth->id, ParameterType::INTEGER)));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $qb1_result = $qb1->getQuery()->getResult();

        $auth_meta = new Usermeta();
        $auth_meta->user_id = $auth->id;
        $auth_meta->meta_key = 'uploads_size';
        $auth_meta->meta_value = (int) $qb1_result[0][1];
        $auth_meta->user = $auth;
        Flight::get('em')->persist($auth_meta);
        Flight::get('em')->flush();

        // -- User meta: notifications count --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(comment.id)')->from('App\Entities\Comment', 'comment')->where($qb1->expr()->eq('comment.user_id', $auth->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $auth_meta = new Usermeta();
        $auth_meta->user_id = $auth->id;
        $auth_meta->meta_key = 'notifications_count';
        $auth_meta->meta_value = (int) $qb1_result[0][1];
        $auth_meta->user = $auth;
        Flight::get('em')->persist($auth_meta);
        Flight::get('em')->flush();

        // -- Make uploads dir --
        if(!file_exists(APP_UPLOAD_PATH . $auth->id)) {
            try {
                mkdir(APP_UPLOAD_PATH . $auth->id, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('Upload error: make dir error.');
            }
        }

        // -- Email --
        Flight::get('phpmailer')->addAddress($auth->user_email, $auth->user_name);
        Flight::get('phpmailer')->Subject = 'User register';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $auth->user_pass . '</i>';
        Flight::get('phpmailer')->send();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' =>  [
                'id' => $auth->id, 
                'create_date' => $auth->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth->user_status,
                'user_name' => $auth->user_name
            ]
        ]);
    }
}
