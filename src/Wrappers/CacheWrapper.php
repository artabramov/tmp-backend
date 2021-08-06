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

class CacheWrapper
{
    protected $em;

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

    public function create() {

        // -- User --
        $user = new User();
        $user->create_date = Flight::datetime();
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = new DateTime('1970-01-01 00:00:00');
        $user->auth_date = new DateTime('1970-01-01 00:00:00');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = 'noreply.1@noreply.no';
        $user->user_phone = '12345678901';
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = 'noname 1';
        $this->em->persist($user);
        $this->em->flush();

        // -- Member --
        $member = new User();
        $member->create_date = Flight::datetime();
        $member->update_date = new DateTime('1970-01-01 00:00:00');
        $member->remind_date = new DateTime('1970-01-01 00:00:00');
        $member->auth_date = new DateTime('1970-01-01 00:00:00');
        $member->user_status = 'pending';
        $member->user_token = $member->create_token();
        $member->user_email = 'noreply.2@noreply.no';
        $member->user_phone = '12345678902';
        $member->user_pass = $member->create_pass();
        $member->user_hash = sha1($member->user_pass);
        $member->user_name = 'noname 2';
        $this->em->persist($member);
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

        // -- Member role --
        $member_role = new UserRole();
        $member_role->create_date = Flight::datetime();
        $member_role->update_date = new DateTime('1970-01-01 00:00:00');
        $member_role->user_id = $member->id;
        $member_role->repo_id = $repo->id;
        $member_role->role_status = 'admin';
        $member_role->user = $member;
        $member_role->repo = $repo;
        $this->em->persist($member_role);
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
        $upload->thumb_path = 'thumb path';
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
    }

    public function read() {

        // --
        $user = Flight::get('em')->find('\App\Entities\User', 1);
        $repo = Flight::get('em')->find('\App\Entities\Repo', 1);
        $post = Flight::get('em')->find('\App\Entities\Post', 1);
        $comment = Flight::get('em')->find('\App\Entities\Comment', 1);
        
        /*
        // -- clear cache --
        if(Flight::get('em')->getCache()->containsCollection('\App\Entities\User', 'user_terms', 1)) {
            Flight::get('em')->getCache()->evictCollection('\App\Entities\User', 'user_terms', 1);
        }
        */

        // -- json --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'update_date' => $user->update_date->format('Y-m-d H:i:s'),
                'remind_date' => $user->remind_date->format('Y-m-d H:i:s'),
                'auth_date' => $user->auth_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_phone' => !empty($user->user_phone) ? $user->user_phone : '',
                'user_name' => $user->user_name,
    
                'user_terms' => call_user_func( 
                    function($user_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $user_terms), 
                            array_map(fn($n) => $n->term_value, $user_terms));
                    }, $user->user_terms->toArray()),
    
                'user_roles' => call_user_func( 
                    function($user_roles) {
                        return array_combine(
                            array_map(fn($n) => $n->repo_id, $user_roles), 
                            array_map(fn($n) => $n->role_status, $user_roles));
                    }, $user->user_roles->toArray()),
            ],
    
            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'update_date' => $repo->update_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,
    
                'repo_terms' => call_user_func( 
                    function($repo_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $repo_terms), 
                            array_map(fn($n) => $n->term_value, $repo_terms));
                    }, $repo->repo_terms->toArray()),
    
                'repo_roles' => call_user_func( 
                    function($repo_roles) {
                        return array_combine(
                            array_map(fn($n) => $n->user_id, $repo_roles), 
                            array_map(fn($n) => $n->role_status, $repo_roles));
                    }, $repo->repo_roles->toArray()),
            ],
    
            'post' => [
                'id' => $post->id, 
                'create_date' => $post->create_date->format('Y-m-d H:i:s'),
                'update_date' => $post->update_date->format('Y-m-d H:i:s'),
                'user_id' => $post->user_id,
                'repo_id' => $post->repo_id,
                'post_status' => $post->post_status,
                'post_title' => $post->post_title,
    
                'post_terms' => call_user_func( 
                    function($post_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $post_terms), 
                            array_map(fn($n) => $n->term_value, $post_terms));
                    }, $post->post_terms->toArray()),
    
                'post_tags' => call_user_func( 
                    function($post_tags) {
                        return array_map(fn($n) => $n->tag_value, $post_tags);
                    }, $post->post_tags->toArray()),

                'post_alerts' => call_user_func( 
                    function($post_alerts) {
                        return array_combine(
                            array_map(fn($n) => $n->user_id, $post_alerts), 
                            array_map(fn($n) => $n->alerts_count, $post_alerts));
                    }, $post->post_alerts->toArray()),
            ],
    
            'comment' => [
                'id' => $comment->id, 
                'create_date' => $comment->create_date->format('Y-m-d H:i:s'),
                'update_date' => $comment->update_date->format('Y-m-d H:i:s'),
                'user_id' => $comment->user_id,
                'post_id' => $comment->post_id,
                'comment_content' => $comment->comment_content,
    
                'comment_uploads' => call_user_func( 
                    function($comment_uploads) {
                        return array_map(fn($n) => [
                            'id' => $n->id,
                            'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                            'update_date' => $n->update_date->format('Y-m-d H:i:s'),
                            'user_id' => $n->user_id,
                            'comment_id' => $n->comment_id,
                            'upload_name' => $n->upload_name,
                            'upload_path' => $n->upload_path,
                            'upload_mime' => $n->upload_mime,
                            'upload_size' => $n->upload_size,
                            'thumb_path' => $n->thumb_path,
                        ], $comment_uploads);
                    }, $comment->comment_uploads->toArray()),
            ],
    
        ]);
    }
}
