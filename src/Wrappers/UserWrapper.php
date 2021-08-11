<?php
namespace App\Wrappers;
use \Flight,
    \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
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
    \App\Entities\UserVolume, // 21..
    \App\Entities\Premium;    // 22..

class UserWrapper
{
    protected $em;

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

    public function insert(string $user_email, string $user_name, string $user_phone = '') {

        $user_email = mb_strtolower($user_email);
        $user_phone = empty($user_phone) ? null : preg_replace('/[^0-9]/', '', $user_phone);

        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('user_email is occupied', 2001);

        } elseif(!empty($user_phone) and $this->em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('user_phone is occupied', 2002);
        }

        // -- Filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::USER_REGISTER_LIMIT) {
            throw new AppException('wait for 60 seconds', 0);
        }

        // -- Create user --
        $user = new User();
        $user->create_date = Flight::datetime();
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = Flight::datetime();
        $user->reset_date = new DateTime('1970-01-01 00:00:00');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_phone = $user_phone;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $this->em->persist($user);
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
        $phpmailer->send();

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
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Member ---
        $member = $this->em->find('\App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('user not found', 0);
        }

        // -- End --
        if($user->id == $member->id) {

            Flight::json([
                'success' => 'true',
                'user' => [
                    'id' => $member->id, 
                    'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                    'user_status' => $member->user_status,
                    'user_email' => $member->user_email,
                    'user_phone' => !empty($member->user_phone) ? $member->user_phone : '',
                    'user_name' => $member->user_name,
                    'user_terms' => call_user_func( 
                        function($user_terms) {
                            return array_combine(
                                array_map(fn($n) => $n->term_key, $user_terms), 
                                array_map(fn($n) => $n->term_value, $user_terms));
                        }, $member->user_terms->toArray()),
                ],
            ]);

        } else {

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

    }

    public function update(string $user_token, string $user_email, string $user_phone, string $user_name) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // --
        $user_email = mb_strtolower($user_email);
        $user_phone = empty($user_phone) ? null : preg_replace('/[^0-9]/', '', $user_phone);

        if(!empty($user_email) and $user_email != $user->user_email and $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('user_email is occupied', 2001);

        } elseif(!empty($user_phone) and $user_phone != $user->user_phone and $this->em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('user_phone is occupied', 0);
        }

        if($user->user_email != $user_email) {
            $user->user_status = 'pending';
        }

        $user->user_email = $user_email;
        $user->user_phone = $user_phone;
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
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- User relations --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('relate_id', 'relate_id');

        $query = $this->em->createNativeQuery("SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id LIMIT :limit OFFSET :offset", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('offset', $offset)
            ->setParameter('limit', self::USER_LIST_LIMIT);
        $users = array_map(fn($n) => $this->em->find('App\Entities\User', $n['relate_id']), $query->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'users_limit' => self::USER_LIST_LIMIT,
            'users_count' => (int) call_user_func( 
                function($terms, $key, $default) {
                    $tmp = $terms->filter(function($el) use ($key) {
                        return $el->term_key == $key;
                    })->first();
                    return empty($tmp) ? $default : $tmp->term_value;
                }, $user->user_terms, 'relations_count', 0 ),

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
            throw new AppException('user not found', 2003);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 2004);

        } elseif(Flight::datetime()->getTimestamp() - $user->remind_date->getTimestamp() > self::USER_SIGNIN_EXPIRES) {
            throw new AppException('user_pass expired', 2005);
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
                'user_phone' => !empty($user->user_phone) ? $user->user_phone : '',
                'user_name' => $user->user_name,
                'user_terms' => call_user_func( 
                    function($user_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $user_terms), 
                            array_map(fn($n) => $n->term_value, $user_terms));
                    }, $user->user_terms->toArray()),
            ],
        ]);
    }

    public function remind(string $user_email) {

        $user_email = mb_strtolower($user_email);

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);

        } elseif(Flight::datetime()->getTimestamp() - $user->remind_date->getTimestamp() < self::USER_REMIND_EXPIRES) {
            throw new AppException('wait for ' . self::USER_REMIND_EXPIRES . ' seconds', 0);
        }

        // -- Filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::USER_REMIND_LIMIT) {
            throw new AppException('wait for 60 seconds', 0);
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
        $phpmailer->send();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function signout(string $user_token) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        $user->user_token = $user->create_token();
        $this->em->persist($user);
        $this->em->flush();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function find(string $user_token, string $search_text) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Search --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->em->createNativeQuery("SELECT id FROM users WHERE (user_email LIKE :search_text OR user_name LIKE :search_text) AND id IN (SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id) LIMIT :limit", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('search_text', '%' . $search_text . '%')
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

}
