<?php
namespace App\Routers;
use \Flight,
    \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \App\Exceptions\AppException,
    \App\Entities\User,
    \App\Entities\UserTerm,
    \App\Entities\Repo,
    \App\Entities\RepoTerm,
    \App\Entities\UserRole,
    \App\Entities\Post,
    \App\Entities\PostTerm,
    \App\Entities\PostTag,
    \App\Entities\PostAlert,
    \App\Entities\Comment,
    \App\Entities\Upload,
    \App\Entities\UserVolume,
    \App\Entities\Premium;

class UserRouter
{
    protected $em;

    const USER_DEFAULT_TIMEZONE = 'Europe/Moscow';
    const USER_REGISTER_LIMIT = 20; // maximum registers number per 1 minute (for all users)
    const USER_REGISTER_SUBJECT = 'User register';
    const USER_REGISTER_BODY = 'One-time pass: ';
    const USER_REMIND_LIMIT = 20; // maximum reminds number per 1 minute
    const USER_REMIND_EXPIRES = 30;
    const USER_REMIND_SUBJECT = 'User remind';
    const USER_REMIND_BODY = 'One-time pass: ';
    const USER_RESET_EXPIRES = 60;
    const USER_SIGNIN_EXPIRES = 180;
    const USER_LIST_LIMIT = 10;
    const USER_FIND_LIMIT = 5; // autofind
    const VOLUME_DEFAULT_SIZE = 1000000;
    const VOLUME_DEFAULT_INTERVAL = 'P20Y';
    const REPO_DEFAULT_NAME = 'My first hub';
    const POST_DEFAULT_STATUS = 'todo';
    const POST_DEFAULT_TITLE = 'Hello, world!';
    const TAG_DEFAULT_VALUE = 'any tag';
    const COMMENT_DEFAULT_CONTENT = 'First comment.';

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

    public function insert(string $user_email, string $user_name) {

        $user_email = mb_strtolower($user_email);

        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('User email is occupied', 203);
        }

        // -- Filter --
        $wrapper = new \App\Wrappers\UserWrapper($this->em);
        $users_count = $wrapper->count_latest();

        //$stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        //$stmt->execute();
        //$users_count = $stmt->fetchOne();

        if($users_count > self::USER_REGISTER_LIMIT) {
            throw new AppException('Wait a bit', 101);
        }

        // -- Create user --
        $user = new User();
        $user->create_date = Flight::datetime();
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = Flight::datetime();
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $this->em->persist($user);
        $this->em->flush();

        // -- User timezone --
        $user_term = new UserTerm();
        $user_term->create_date = Flight::datetime();
        $user_term->update_date = new DateTime('1970-01-01 00:00:00');
        $user_term->user_id = $user->id;
        $user_term->term_key = 'user_timezone';
        $user_term->term_value = self::USER_DEFAULT_TIMEZONE;
        $user_term->user = $user;
        $this->em->persist($user_term);
        $this->em->flush();

        // -- User volume --
        $user_volume = new UserVolume();
        $user_volume->create_date = Flight::datetime();
        $user_volume->update_date = new DateTime('1970-01-01 00:00:00');
        $user_volume->expires_date = Flight::datetime()->add(new DateInterval(self::VOLUME_DEFAULT_INTERVAL));
        $user_volume->user_id = $user->id;
        $user_volume->volume_size = self::VOLUME_DEFAULT_SIZE;
        $this->em->persist($user_volume);
        $this->em->flush();

        // -- Repo --
        $repo = new Repo();
        $repo->create_date = Flight::datetime();
        $repo->update_date = new DateTime('1970-01-01 00:00:00');
        $repo->user_id = $user->id;
        $repo->repo_name = self::REPO_DEFAULT_NAME;
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
        $post->post_status = self::POST_DEFAULT_STATUS;
        $post->post_title = self::POST_DEFAULT_TITLE;
        $this->em->persist($post);
        $this->em->flush();

        // -- Tag --
        $tag = new PostTag();
        $tag->create_date = Flight::datetime();
        $tag->update_date = new DateTime('1970-01-01 00:00:00');
        $tag->post_id = $post->id;
        $tag->tag_value = self::TAG_DEFAULT_VALUE;
        $tag->post = $post;
        $this->em->persist($tag);
        $this->em->flush();

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = Flight::datetime();
        $comment->update_date = new DateTime('1970-01-01 00:00:00');
        $comment->user_id = $user->id;
        $comment->post_id = $post->id;
        $comment->comment_content = self::COMMENT_DEFAULT_CONTENT;
        $this->em->persist($comment);
        $this->em->flush();

        // -- Email --
        $phpmailer = Flight::get('phpmailer');
        $phpmailer->addAddress($user->user_email, $user->user_name);
        $phpmailer->Subject = self::USER_REGISTER_SUBJECT;
        $phpmailer->Body = self::USER_REGISTER_BODY . $user->user_pass;

        if(!$phpmailer->send()) {
            throw new AppException('SMTP error', 110);
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id
            ],
        ]);
    }

    public function select(string $user_token, int $user_id) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Member ---
        $member = $this->em->find('\App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('User not found', 201);
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $member->id, 
                'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                'user_status' => $member->user_status,
                'user_name' => $member->user_name,
            ],
        ]);

    }

    public function update(string $user_token, string $user_name) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        $user->update_date = Flight::datetime();
        $user->user_name = $user_name;
        $this->em->persist($user);
        $this->em->flush();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function list(string $user_token, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- User relations --
        $wrapper = new \App\Wrappers\UserWrapper($this->em);
        $users = $wrapper->select_related($user->id, self::USER_LIST_LIMIT, $offset);

        // -- End --
        Flight::json([
            'success' => 'true',

            'users_limit' => self::USER_LIST_LIMIT,
            'users_count' => (int) call_user_func(function($user_id, $term_key) {
                $term = $this->em->getRepository('\App\Entities\UserTerm')->findOneBy(['user_id' => $user_id, 'term_key' => $term_key]);
                return empty($term) ? 0 : $term->term_value;
            }, $user->id, 'relations_count'),

            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_email' => $n->user_email,
                'user_status' => $n->user_status,
                'user_name' => $n->user_name
            ], $users)
        ]);
    }

    public function signin(string $user_email, string $user_pass) {

        $user_email = mb_strtolower($user_email);

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email, 'user_hash' => sha1($user_pass)]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);

        } elseif(Flight::datetime()->getTimestamp() - $user->remind_date->getTimestamp() > self::USER_SIGNIN_EXPIRES) {
            throw new AppException('User password expired', 204);
        }

        $user->user_status = 'approved';
        $user->user_pass = null;
        $user->user_hash = null;
        $this->em->persist($user);
        $this->em->flush();

        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name,
                
                'users_terms' => (array) call_user_func(function($user) {
                    $terms = Flight::get_terms($user);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms));
                }, $user),
                
            ],
        ]);
    }

    public function remind(string $user_email) {

        $user_email = mb_strtolower($user_email);

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);

        } elseif(Flight::datetime()->getTimestamp() - $user->remind_date->getTimestamp() < self::USER_REMIND_EXPIRES) {
            throw new AppException('Wait a bit', 101);
        }

        // -- Filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::USER_REMIND_LIMIT) {
            throw new AppException('Wait a bit', 101);
        }

        // -- Update user --
        $user->remind_date = Flight::datetime();
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $this->em->persist($user);
        $this->em->flush();

        // -- Email --
        $phpmailer = Flight::get('phpmailer');
        $phpmailer->addAddress($user->user_email, $user->user_name);
        $phpmailer->Subject = self::USER_REMIND_SUBJECT;
        $phpmailer->Body = self::USER_REMIND_BODY . $user->user_pass;

        if(!$phpmailer->send()) {
            throw new AppException('SMTP error', 110);
        }

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function signout(string $user_token) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        $user->user_token = $user->create_token();
        $this->em->persist($user);
        $this->em->flush();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function find(string $user_token, string $like_text) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Search --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->em->createNativeQuery("SELECT id FROM users WHERE (user_email LIKE :like_text OR user_name LIKE :like_text) AND id IN (SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id) LIMIT :limit", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('like_text', '%' . $like_text . '%')
            ->setParameter('limit', self::USER_FIND_LIMIT);

        $users = array_map(fn($n) => $this->em->find('App\Entities\User', $n['id']), $query->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_status' => $n->user_status,
                'user_email' => $n->user_email,
                'user_name' => $n->user_name
            ], $users)
        ]);
    }

    public function auth(string $user_token) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name,

                'user_terms' => (array) call_user_func(function($user) {
                    $terms = Flight::get_terms($user);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms));
                }, $user),

                'user_alerts' => (int) Flight::count_alerts($user, $user),

            ],
        ]);

    }

}
