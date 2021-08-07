<?php
namespace App\Wrappers;
use \Flight,
    \DateTime,
    \DateInterval,
    \App\Exceptions\AppException,
    \App\Entities\User,       // 10..
    \App\Entities\UserTerm,   // 11..
    \App\Entities\Repo,       // 12..
    \App\Entities\RepoTerm,   // 13..
    \App\Entities\UserRole,   // 14..
    \App\Entities\Post,       // 15..
    \App\Entities\PostTerm,   // 16..
    \App\Entities\PostTag,    // 17..
    \App\Entities\PostAlert,  // 18..
    \App\Entities\Comment,    // 19..
    \App\Entities\Upload,     // 20..
    \App\Entities\UserVolume; // 21..

class UserWrapper
{
    protected $em;

    const USER_REMIND_EXPIRES = 30;
    const USER_RESET_EXPIRES = 60;
    const USER_SIGNIN_EXPIRES = 180;
    const VOLUME_DEFAULT_SIZE = 1000000;
    const VOLUME_DEFAULT_INTERVAL = 'P20Y';

    public function __construct($em) {
        $this->em = $em;
    }

    public function __set($key, $value) {
        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

    public function __get($key) {
        return property_exists($this, $key) ? $this->$key : null;
    }

    public function __isset( $key ) {
        return property_exists($this, $key) ? !empty($this->$key) : false;
    }

    public function create(string $user_email, string $user_name, string $user_phone = '') {

        $user_email = mb_strtolower($user_email);
        $user_phone = preg_replace('/[^0-9]/', '', $user_phone) ? !empty($user_phone) : null;

        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('user_email is occupied', 2001);

        } elseif(!empty($user_phone) and $em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('user_phone is occupied', 2002);
        }

        // -- User --
        $user = new User();
        $user->create_date = Flight::datetime();
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = new DateTime('1970-01-01 00:00:00');
        $user->reset_date = new DateTime('1970-01-01 00:00:00');
        $user->auth_date = new DateTime('1970-01-01 00:00:00');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_phone = $user_phone;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $this->em->persist($user);
        $this->em->flush();

        // -- Repo --
        $repo = new Repo();
        $repo->create_date = Flight::datetime();
        $repo->update_date = new DateTime('1970-01-01 00:00:00');
        $repo->user_id = $user->id;
        $repo->repo_name = 'First hub';
        $this->em->persist($repo);
        $this->em->flush();

        // -- User role --
        $user_role = new UserRole();
        $user_role->create_date = Flight::datetime();
        $user_role->update_date = new DateTime('1970-01-01 00:00:00');
        $user_role->user_id = $user->id;
        $user_role->repo_id = $repo->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->repo = $repo;
        $this->em->persist($user_role);
        $this->em->flush();

        // -- Post --
        $post = new Post();
        $post->create_date = Flight::datetime();
        $post->update_date = new DateTime('1970-01-01 00:00:00');
        $post->user_id = $user->id;
        $post->repo_id = $repo->id;
        $post->post_status = 'doing';
        $post->post_title = 'Hello, world';
        $this->em->persist($post);
        $this->em->flush();

        // -- Tag --
        $tag = new PostTag();
        $tag->create_date = Flight::datetime();
        $tag->update_date = new DateTime('1970-01-01 00:00:00');
        $tag->post_id = $post->id;
        $tag->tag_value = 'any tag';
        $tag->post = $post;
        $this->em->persist($tag);
        $this->em->flush();

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = Flight::datetime();
        $comment->update_date = new DateTime('1970-01-01 00:00:00');
        $comment->user_id = $user->id;
        $comment->post_id = $post->id;
        $comment->comment_content = 'First comment';
        $this->em->persist($comment);
        $this->em->flush();

        // -- Upload --
        $upload = new Upload();
        $upload->create_date = Flight::datetime();
        $upload->update_date = new DateTime('1970-01-01 00:00:00');
        $upload->user_id = $user->id;
        $upload->comment_id = $comment->id;
        $upload->upload_name = 'upload name';
        $upload->upload_path = 'upload path';
        $upload->upload_mime = 'upload mime';
        $upload->upload_size = 100;
        $upload->thumb_path = 'thumb_path';
        $upload->comment = $comment;
        $this->em->persist($upload);
        $this->em->flush();

        // -- User volume --
        $user_volume = new UserVolume();
        $user_volume->create_date = Flight::datetime();
        $user_volume->update_date = new DateTime('1970-01-01 00:00:00');
        $user_volume->expires_date = new DateTime('2030-01-01 00:00:00');
        //$user_volume->expire_date = clone Flight::get('date')->add(new DateInterval(VOL_DEFAULT_EXPIRE));
        $user_volume->user_id = $user->id;
        $user_volume->volume_size = 500;
        $this->em->persist($user_volume);
        $this->em->flush();

        // -- End --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id
            ]
        ]);


        /*
        $em = Flight::get('em');
        $user_email = mb_strtolower((string) Flight::request()->query['user_email']);
        $user_name = (string) Flight::request()->query['user_name'];
        $user_phone = preg_replace('/[^0-9]/', '', (string) Flight::request()->query['user_phone']);
        $user_phone = empty($user_phone) ? null : $user_phone;

        if($em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('User error: user_email is occupied.');

        } elseif(!empty($user_phone) and $em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('User error: user_phone is occupied.');
        }

        // -- User --
        $user = new User();
        $user->create_date = Flight::get('date');
        $user->update_date = Flight::get('zero');
        $user->remind_date = Flight::get('date');
        $user->auth_date = Flight::get('zero');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_phone = $user_phone;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $em->persist($user);
        $em->flush();

        // -- Hub --
        $hub = new Hub();
        $hub->create_date = Flight::get('date');
        $hub->update_date = Flight::get('zero');
        $hub->user_id = $user->id;
        $hub->hub_name = 'First hub';
        $em->persist($hub);
        $em->flush();

        // -- User role --
        $user_role = new Role();
        $user_role->create_date = Flight::get('date');
        $user_role->update_date = Flight::get('zero');
        $user_role->user_id = $user->id;
        $user_role->hub_id = $hub->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->hub = $hub;
        $em->persist($user_role);
        $em->flush();

        // -- User vol --
        $user_vol = new Vol();
        $user_vol->create_date = Flight::get('date');
        $user_vol->update_date = Flight::get('zero');
        $user_vol->expire_date = clone Flight::get('date')->add(new \DateInterval(VOL_DEFAULT_EXPIRE));
        $user_vol->user_id = $user->id;
        $user_vol->vol_size = VOL_DEFAULT_SIZE;
        $em->persist($user_vol);
        $em->flush();

        // -- Post --
        $post = new Post();
        $post->create_date = Flight::get('date');
        $post->update_date = Flight::get('zero');
        $post->user_id = $user->id;
        $post->hub_id = $hub->id;
        $post->post_status = 'doing';
        $post->post_title = 'Hello, world';
        $em->persist($post);
        $em->flush();

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = Flight::get('date');
        $comment->update_date = Flight::get('zero');
        $comment->user_id = $user->id;
        $comment->post_id = $post->id;
        $comment->comment_content = 'First comment';
        $comment->post = $post;
        $em->persist($comment);
        $em->flush();

        // -- Dir --
        if(!file_exists(UPLOAD_PATH . $user->id)) {
            try {
                mkdir(UPLOAD_PATH . $user->id, 0777, true);
            } catch (\Exception $e) {
                throw new AppException('Upload error: make dir error.');
            }
        }

        // -- Email --
        $phpmailer = Flight::get('phpmailer');
        $phpmailer->addAddress($user->user_email, $user->user_name);
        $phpmailer->Subject = 'User register';
        $phpmailer->Body = 'One-time pass: <i>' . $user->user_pass . '</i>';
        $phpmailer->send();

        // -- End --
        Flight::json([
            'success' => 'true',
            'user_id' => $user->id
        ]);
        */
    }
}
