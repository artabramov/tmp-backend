<?php
namespace App\Routes;
use \Flight, 
    \DateTime, 
    \DateInterval,
    \Doctrine\DBAL\ParameterType,
    \App\Exceptions\AppException,
    \App\Entities\User, 
    \App\Entities\Usermeta, 
    \App\Entities\Role, 
    \App\Entities\Vol, 
    \App\Entities\Hub, 
    \App\Entities\Hubmeta, 
    \App\Entities\Post, 
    \App\Entities\Postmeta, 
    \App\Entities\Tag, 
    \App\Entities\Comment, 
    \App\Entities\Upload;

class UserRegister
{
    public function do() {

        // -- Vars --

        $user_email = (string) Flight::request()->query['user_email'];
        $user_name = (string) Flight::request()->query['user_name'];

        // -- User --

        if(Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('User error: user_email is occupied.');
        }

        $user = new User();
        $user->remind_date = new DateTime('now');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_email = $user_email;
        $user->user_name = $user_name;
        Flight::get('em')->persist($user);
        Flight::get('em')->flush();



        $cache = Flight::get('em')->getCache();
        $cache->evictEntity('\App\Entities\User', $user->id);


        /*
        $cache->containsEntity('Entity\State', 1)      // Check if the cache exists
        $cache->evictEntity('Entity\State', 1);        // Remove an entity from cache
        $cache->evictEntityRegion('Entity\State');     // Remove all entities from cache
        
        $cache->containsCollection('Entity\State', 'cities', 1);   // Check if the cache exists
        $cache->evictCollection('Entity\State', 'cities', 1);      // Remove an entity collection from cache
        $cache->evictCollectionRegion('Entity\State', 'cities');   // Remove all collections from cache
        */


        /*
        $user_meta = new Usermeta();
        $user_meta->user_id = $user->id;
        $user_meta->meta_key = 'user_meta';
        $user_meta->meta_value = 'user meta';
        $user_meta->user = $user;
        Flight::get('em')->persist($user_meta);
        Flight::get('em')->flush();
        */


        /*

        // -- Hub --

        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $user->id;
        $hub->hub_name = 'My hub';
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- User role --

        $user_role = new Role();
        $user_role->user_id = $user->id;
        $user_role->hub_id = $hub->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->hub = $hub;
        Flight::get('em')->persist($user_role);
        Flight::get('em')->flush();

        // -- User vol --

        $user_vol = new Vol();
        $user_vol->user_id = $user->id;
        $user_vol->expire_date = new DateTime('now');
        $user_vol->expire_date->add(new DateInterval(VOL_DEFAULT_EXPIRE));
        $user_vol->vol_size = VOL_DEFAULT_SIZE;
        Flight::get('em')->persist($user_vol);
        Flight::get('em')->flush();

        // -- Post --

        $post = new Post();
        $post->user_id = $user->id;
        $post->hub_id = $hub->id;
        $post->post_status = 'todo';
        $post->post_title = 'Hello, world!';
        Flight::get('em')->persist($post);
        Flight::get('em')->flush();

        // -- Comment --

        $comment = new Comment();
        $comment->user_id = $user->id;
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
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.user_id', $user->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $user_meta = new Usermeta();
        $user_meta->user_id = $user->id;
        $user_meta->meta_key = 'roles_count';
        $user_meta->meta_value = (int) $qb1_result[0][1];
        $user_meta->user = $user;
        Flight::get('em')->persist($user_meta);
        Flight::get('em')->flush();

        // -- User meta: uploads size --

        $qb2 = Flight::get('em')->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.user_id', Flight::get('em')->getConnection()->quote($user->id, ParameterType::INTEGER)));

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('sum(upload.upload_size)')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $qb1_result = $qb1->getQuery()->getResult();

        $user_meta = new Usermeta();
        $user_meta->user_id = $user->id;
        $user_meta->meta_key = 'uploads_size';
        $user_meta->meta_value = (int) $qb1_result[0][1];
        $user_meta->user = $user;
        Flight::get('em')->persist($user_meta);
        Flight::get('em')->flush();

        // -- User meta: notices count --

        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(comment.id)')->from('App\Entities\Comment', 'comment')->where($qb1->expr()->eq('comment.user_id', $user->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $user_meta = new Usermeta();
        $user_meta->user_id = $user->id;
        $user_meta->meta_key = 'notices_count';
        $user_meta->meta_value = (int) $qb1_result[0][1];
        $user_meta->user = $user;
        Flight::get('em')->persist($user_meta);
        Flight::get('em')->flush();

        // -- Make uploads dir --

        if(!file_exists(APP_UPLOAD_PATH . $user->id)) {
            try {
                mkdir(APP_UPLOAD_PATH . $user->id, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('Upload error: make dir error.');
            }
        }

        // -- Email --

        Flight::get('phpmailer')->addAddress($user->user_email, $user->user_name);
        Flight::get('phpmailer')->Subject = 'User register';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $user->user_pass . '</i>';
        Flight::get('phpmailer')->send();
        */

        // -- End --

        Flight::json([ 
            'success' => 'true',
            'user' =>  [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_name' => $user->user_name
            ]
        ]);
    }
}
