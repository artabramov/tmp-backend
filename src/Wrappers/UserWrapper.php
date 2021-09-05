<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\User;
    

class UserWrapper
{
    protected $em;
    protected $time;

    const REGISTER_TIMEZONE = 'Europe/Moscow'; // default timezone
    const REGISTER_LIMIT = 20; // users inserts number in 1 minute (for any users)
    const REMIND_LIMIT = 20; // maximum number of pass reminds (in 1 minute)
    const REMIND_EXPIRES = 30; // minimal time between pass reminds to email (in seconds)
    const SIGNIN_EXPIRES = 1800; // hash expires in seconds
    const LIST_LIMIT = 10; // users number in one select result
    const FIND_LIMIT = 5; // users number in autofind result

    /*
    const VOLUME_DEFAULT_SIZE = 1000000;
    const VOLUME_DEFAULT_INTERVAL = 'P20Y';
    const REPO_DEFAULT_NAME = 'My first hub';
    const POST_DEFAULT_STATUS = 'todo';
    const POST_DEFAULT_TITLE = 'Hello, world!';
    const TAG_DEFAULT_VALUE = 'any tag';
    const COMMENT_DEFAULT_CONTENT = 'First comment.';
    */

    public function __construct(\Doctrine\ORM\EntityManager $em, \App\Services\Time $time) {
        $this->em = $em;
        $this->time = $time;
    }

    public function __set(string $key, mixed $value) {
        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

    public function __get(string $key) {
        return property_exists($this, $key) ? $this->$key : null;
    }

    public function __isset(string $key) {
        return property_exists($this, $key) ? !empty($this->$key) : false;
    }

    public function token() {
        return sha1(date('U')) . bin2hex(random_bytes(20));
    }

    public function pass() {
        $pass_symbols = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
        $pass_len = 8;
        $symbols_length = mb_strlen($pass_symbols, 'utf-8') - 1;
        $user_pass = '';
        for($i = 0; $i < $pass_len; $i++) {
            $user_pass .= $pass_symbols[random_int(0, $symbols_length)];
        }
        return $user_pass;
    }

    public function register(string $user_email, string $user_name, string $user_timezone = '') {

        $user_email = mb_strtolower($user_email);

        // Filter
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::REGISTER_LIMIT) {
            Halt::throw(1001); // wait a bit
        }

        // User
        $user = new User();
        $user->create_date = $this->time->datetime;
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = $this->time->datetime;
        $user->user_status = 'pending';
        $user->user_token = $this->token();
        $user->user_email = $user_email;
        $user->user_pass = $this->pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $user->user_timezone = empty($user_timezone) ? self::REGISTER_TIMEZONE : $user_timezone;

        // Pre-insert checking
        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user->user_email])) {
            Halt::throw(1120); // user_email is occupied
        }

        // Save
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function remind(string $user_email) {

        $user_email = mb_strtolower($user_email);

        // -- Pre filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::REMIND_LIMIT) {
            Halt::throw(1001); // wait a bit
        }

        // -- User filter --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($user)) {
            Halt::throw(1101); // user not found

        } elseif($user->user_status == 'trash') {
            Halt::throw(1112); // user_status is trash

        } elseif($this->time->datetime->getTimestamp() - $user->remind_date->getTimestamp() < self::REMIND_EXPIRES) {
            Halt::throw(1001); // wait a bit
        }

        // -- Update user --
        $user->remind_date = $this->time->datetime;
        $user->user_pass = $this->pass();
        $user->user_hash = sha1($user->user_pass);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function signin(string $user_email, string $user_pass) {

        $user_email = mb_strtolower($user_email);
        $user_hash = sha1($user_pass);

        // -- User --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email, 'user_hash' => $user_hash]);

        if(empty($user)) {
            Halt::throw(1101); // user not found

        } elseif($user->user_status == 'trash') {
            Halt::throw(1112); // user_status is trash

        } elseif($this->time->datetime->getTimestamp() - $user->remind_date->getTimestamp() > self::SIGNIN_EXPIRES) {
            Halt::throw(1117); // user_hash expired
        }

        $user->user_status = 'approved';
        $user->user_pass = null;
        $user->user_hash = null;
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function auth(string $user_token) {

        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            Halt::throw(1101); // user not found

        } elseif($user->user_status == 'trash') {
            Halt::throw(1112); // user_status is trash
        }

        return $user;
    }

    // select user by email
    public function byemail(string $user_email) {

        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($user)) {
            Halt::throw(1101); // user not found

        } elseif($user->user_status == 'trash') {
            Halt::throw(1112); // user_status is trash
        }

        return $user;
    }

    public function signout(\App\Entities\User $user) {

        $user->user_token = $this->token();
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function update(\App\Entities\User $user, string $user_name, string $user_timezone) {

        $user->update_date = $this->time->datetime;
        $user->user_name = $user_name;
        $user->user_timezone = $user_timezone;
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function select(int $user_id) {

        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['id' => $user_id]);

        if(empty($user)) {
            Halt::throw(1101); // user not found
        }

        return $user;
    }

    public function list(int $user_id, int $offset) {

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('relate_id', 'relate_id');

        $query = $this->em->createNativeQuery("SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id ORDER BY relate_id ASC LIMIT :limit OFFSET :offset", $rsm)
            ->setParameter('user_id', $user_id)
            ->setParameter('offset', $offset)
            ->setParameter('limit', self::LIST_LIMIT);
        $users = array_map(fn($n) => $this->em->find('App\Entities\User', $n['relate_id']), $query->getResult());

        return $users;
    }

    public function find(int $user_id, string $value) {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->em->createNativeQuery("SELECT id FROM users WHERE (user_email LIKE :value OR user_name LIKE :value) AND id IN (SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id) ORDER BY relate_id ASC LIMIT :limit", $rsm)
            ->setParameter('user_id', $user_id)
            ->setParameter('value', '%' . $value . '%')
            ->setParameter('limit', self::FIND_LIMIT);

        $users = array_map(fn($n) => $this->em->find('App\Entities\User', $n['id']), $query->getResult());
        return $users;
    }

}
