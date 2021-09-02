<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Wrappers\UserWrapper;

class UserRouter
{
    protected $em;
    protected $time;
    protected $phpmailer;

    /*
    const LIST_LIMIT = 10; // users number in one select result
    const REGISTER_SUBJECT = 'User register'; // register email subject
    const REGISTER_BODY = 'One-time pass: '; // register email body
    const REMIND_EXPIRES = 30; // minimal time between pass reminds to email (in seconds)
    const REMIND_LIMIT = 20; // maximum number of pass reminds (in 1 minute)
    const REMIND_SUBJECT = 'User remind';
    const REMIND_BODY = 'One-time pass: ';
    const SIGNIN_EXPIRES = 180; // hash expires in seconds
    const FIND_LIMIT = 5; // limit for autofind
    */

    /*
    const VOLUME_DEFAULT_SIZE = 1000000;
    const VOLUME_DEFAULT_INTERVAL = 'P20Y';
    const REPO_DEFAULT_NAME = 'My first hub';
    const POST_DEFAULT_STATUS = 'todo';
    const POST_DEFAULT_TITLE = 'Hello, world!';
    const TAG_DEFAULT_VALUE = 'any tag';
    const COMMENT_DEFAULT_CONTENT = 'First comment.';
    */

    public function __construct($em, $time, $phpmailer) {
        $this->em = $em;
        $this->time = $time;
        $this->phpmailer = $phpmailer;
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

    private function count_alerts(int $user_id) {

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $q1 = $this->em
            ->createNativeQuery("SELECT alerts_count FROM vw_users_alerts WHERE user_id = :user_id LIMIT 1", $rsm)
            ->setParameter('user_id', $user_id);

        $q1_res = $q1->getResult();
        return !empty($q1_res) ? $q1_res[0]['alerts_count'] : 0;
    }

    private function select_terms(int $user_id) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('term.id')
            ->from('App\Entities\UserTerm', 'term')
            ->where($qb1->expr()->eq('term.user_id', $user_id));

        $qb1_result = $qb1->getQuery()->getResult();
        $terms = array_map(fn($n) => $this->em->find('App\Entities\UserTerm', $n['id']), $qb1_result);
        return $terms;
    }

    private function select_related(int $user_id) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('relate_id', 'relate_id');

        $query = $this->em->createNativeQuery("SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id LIMIT :limit OFFSET :offset", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('offset', $offset)
            ->setParameter('limit', self::FIND_LIMIT);
        $users = array_map(fn($n) => $this->em->find('App\Entities\User', $n['relate_id']), $query->getResult());
        return $users;
    }

    public function insert(string $user_email, string $user_name, string $user_timezone = '') {

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->insert([
            'user_email' => $user_email, 
            'user_name' => $user_name, 
            'user_timezone' => $user_timezone
        ]);

        /*
        $user_timezone = empty($user_timezone) ? self::DEFAULT_TIMEZONE : $user_timezone;
        $user_email = mb_strtolower($user_email);

        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            Halt::throw(1120); // user_email is occupied
        }

        // -- Filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::REGISTER_LIMIT) {
            Halt::throw(1001); // wait a bit
        }

        // -- User --
        $user = new User();
        $user->create_date = $this->date->datetime;
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = $this->date->datetime;
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $user->user_timezone = $user_timezone;
        $this->em->persist($user);
        $this->em->flush();
        
        // -- User term 1 --
        $term = new UserTerm();
        $term->create_date = Flight::get('date')->datetime;
        $term->update_date = new DateTime('1970-01-01 00:00:00');
        $term->user_id = $user->id;
        $term->term_key = 'key_1';
        $term->term_value = 'value 1';
        $this->em->persist($term);
        $this->em->flush();

        // -- User term 2 --
        $term = new UserTerm();
        $term->create_date = Flight::get('date')->datetime;
        $term->update_date = new DateTime('1970-01-01 00:00:00');
        $term->user_id = $user->id;
        $term->term_key = 'key_2';
        $term->term_value = 'value 2';
        $this->em->persist($term);
        $this->em->flush();
        */

        /*
        // -- User volume --
        $user_volume = new UserVolume();
        $user_volume->create_date = Flight::get('time')->datetime;
        $user_volume->update_date = new DateTime('1970-01-01 00:00:00');
        $user_volume->expires_date = Flight::get('time')->datetime->add(new DateInterval(self::VOLUME_DEFAULT_INTERVAL));
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
        */

        /*
        // -- Email --
        $this->phpmailer->addAddress($user->user_email, $user->user_name);
        $this->phpmailer->Subject = self::REGISTER_SUBJECT;
        $this->phpmailer->Body = self::REGISTER_BODY . $user->user_pass;
        $this->phpmailer->send();
        */

        // -- End --
        return [
            'success' => 'true',
            'user' => [
                'id' => $user->id
            ],
        ];
    }

    public function select(string $user_token, int $user_id) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);
        }

        // -- Member ---
        $member = $this->em->find('\App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('user not found', 1101);
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

    public function update(string $user_token, string $user_name, string $user_timezone) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);
        }

        $user->update_date = $this->date->datetime;
        $user->user_name = $user_name;
        $user->user_timezone = $user_timezone;
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
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);
        }

        // -- User relations --
        $wrapper = new \App\Wrappers\UserWrapper($this->em);
        $users = $wrapper->select_related($user->id, self::LIST_LIMIT, $offset);

        // -- End --
        Flight::json([
            'success' => 'true',

            'users_limit' => self::LIST_LIMIT,

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
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);

        } elseif($this->date->datetime->getTimestamp() - $user->remind_date->getTimestamp() > self::SIGNIN_EXPIRES) {
            throw new AppException('user_hash expired', 1115);
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

                'user_terms' => (array) call_user_func(function($user_id) {
                    $terms = $this->select_terms($user_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $user->id),
                
                'alerts_count' => $this->count_alerts($user->id)
            ],
        ]);
    }

    public function remind(string $user_email) {

        $user_email = mb_strtolower($user_email);

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($user)) {
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);

        } elseif($this->date->datetime->getTimestamp() - $user->remind_date->getTimestamp() < self::REMIND_EXPIRES) {
            throw new AppException('wait a bit', 1001);
        }

        // -- Filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::REMIND_LIMIT) {
            throw new AppException('wait a bit', 1001);
        }

        // -- Update user --
        $user->remind_date = $this->date->datetime;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $this->em->persist($user);
        $this->em->flush();

        // -- Email --
        $this->phpmailer->addAddress($user->user_email, $user->user_name);
        $this->phpmailer->Subject = self::REMIND_SUBJECT;
        $this->phpmailer->Body = self::REMIND_BODY . $user->user_pass;
        $this->phpmailer->send();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function signout(string $user_token) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);
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
        /*
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->em->createNativeQuery("SELECT id FROM users WHERE (user_email LIKE :like_text OR user_name LIKE :like_text) AND id IN (SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id) LIMIT :limit", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('like_text', '%' . $like_text . '%')
            ->setParameter('limit', self::USER_FIND_LIMIT);

        $users = array_map(fn($n) => $this->em->find('App\Entities\User', $n['id']), $query->getResult());
        */

        // -- End --
        Flight::json([
            'success' => 'true',

            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_status' => $n->user_status,
                'user_email' => $n->user_email,
                'user_name' => $n->user_name
            ], $this->select_related($user->id))
        ]);
    }

    public function auth(string $user_token) {

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 1101);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 1110);
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
                'user_timezone' => $user->user_timezone,

                'user_terms' => (array) call_user_func(function($user_id) {
                    $terms = $this->select_terms($user_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $user->id),

                'alerts_count' => $this->count_alerts($user->id)
            ],
        ]);

    }

}
