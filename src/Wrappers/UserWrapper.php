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

    const INSERT_TIMEZONE = 'Europe/Moscow'; // default timezone
    const INSERT_LIMIT = 20; // users inserts number in 1 minute (for any users)

    /*
    const LIST_LIMIT = 10; // users number in one select result
    const REMIND_EXPIRES = 30; // minimal time between pass reminds to email (in seconds)
    const REMIND_LIMIT = 20; // maximum number of pass reminds (in 1 minute)
    const SIGNIN_EXPIRES = 180; // hash expires in seconds
    const FIND_LIMIT = 5; // limit for autofind
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

    public function create_token() {
        return sha1(date('U')) . bin2hex(random_bytes(20));
    }

    public function create_pass() {
        $pass_symbols = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
        $pass_len = 8;
        $symbols_length = mb_strlen($pass_symbols, 'utf-8') - 1;
        $user_pass = '';
        for($i = 0; $i < $pass_len; $i++) {
            $user_pass .= $pass_symbols[random_int(0, $symbols_length)];
        }
        return $user_pass;
    }

    public function insert(array $data) {

        $user_email = mb_strtolower($data['user_email']);
        $user_name = $data['user_name'];
        $user_timezone = empty($data['user_timezone']) ? self::INSERT_TIMEZONE : $data['user_timezone'];

        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            Halt::throw(1120); // user_email is occupied
        }

        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::INSERT_LIMIT) {
            Halt::throw(1001); // wait a bit
        }

        $user = new User();
        $user->create_date = $this->time->datetime;
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = $this->time->datetime;
        $user->user_status = 'pending';
        $user->user_token = $this->create_token();
        $user->user_email = $user_email;
        $user->user_pass = $this->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $user->user_timezone = $user_timezone;
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function update(\App\Entities\User $user, array $data) {

        /*
        $remind_date = empty($data['remind_date']) ? $user->remind_date : $data['remind_date'];
        $user_status = empty($data['user_status']) ? $user->user_status : $data['user_status'];
        $user_token = empty($data['user_token']) ? $user->user_token : $data['user_token'];
        $user_email = empty($data['user_email']) ? $user->user_email : $data['user_email'];
        $user_pass = empty($data['user_pass']) ? $user->user_pass : $data['user_pass'];
        $user_hash = empty($data['user_hash']) ? $user->user_hash : $data['user_hash'];
        */


        //$user_name = empty($data['user_name']) ? $user->user_name : $data['user_name'];
        //$user_timezone = empty($data['user_timezone']) ? $user->user_timezone : $data['user_timezone'];

        foreach($data as $key => $value) {
            $user->$key = $value;
        }

        if($user->user_status == 'trash') {
            Halt::throw(1112); // user_status is trash
        }

        $user->update_date = $this->time->datetime;
        $user->user_name = $user_name;
        $user->user_timezone = $user_timezone;
        $this->em->persist($user);
        $this->em->flush();

    }



}
