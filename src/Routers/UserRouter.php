<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Services\Email,
    \App\Wrappers\UserWrapper,
    \App\Wrappers\UserTermWrapper;

class UserRouter
{
    protected $em;
    protected $time;

    public function __construct($em, $time) {
        $this->em = $em;
        $this->time = $time;
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

    /*
    private function count_alerts(int $user_id) {

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $q1 = $this->em
            ->createNativeQuery("SELECT alerts_count FROM vw_users_alerts WHERE user_id = :user_id LIMIT 1", $rsm)
            ->setParameter('user_id', $user_id);

        $q1_res = $q1->getResult();
        return !empty($q1_res) ? $q1_res[0]['alerts_count'] : 0;
    }
    */

    public function register(string $user_email, string $user_name, string $user_timezone = '') {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->register($user_email, $user_name, $user_timezone);

        $term_wrapper = new UserTermWrapper($this->em, $this->time);
        $term_1 = $term_wrapper->insert($user->id, 'key 1', 'value 1');
        $term_2 = $term_wrapper->insert($user->id, 'key 2', 'value 2');
        $term_3 = $term_wrapper->insert($user->id, 'key 3', 'value 3');

        Email::send(
            $user->user_email,
            $user->user_name,
            'User register',
            'One-time pass: ' . $user->user_pass
        );

        return [
            'success' => 'true',
            'user' => ['id' => $user->id],
        ];
    }

    public function remind(string $user_email) {

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->remind($user_email);

        Email::send(
            $user->user_email,
            $user->user_name,
            'User remind',
            'One-time pass: ' . $user->user_pass
        );

        return [
            'success' => 'true'
        ];
    }

    public function signin(string $user_email, string $user_pass) {

        $user_email = mb_strtolower($user_email);

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->signin($user_email, $user_pass);

        return [
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name,
                'user_timezone' => $user->user_timezone,

                'user_terms' => (array) call_user_func(function($user_id) {
                    $term_wrapper = new UserTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($user_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $user->id),

                /*
                'alerts_count' => $this->count_alerts($user->id)
                */
            ],
        ];
    }

    public function signout(string $user_token) {

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->auth($user_token);
        $user = $wrapper->signout($user);

        return [
            'success' => 'true'
        ];
    }

    public function update(string $user_token, string $user_name, string $user_timezone) {

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->auth($user_token);
        $user = $wrapper->update($user, $user_name, $user_timezone);

        return[
            'success' => 'true'
        ];
    }

    public function auth(string $user_token) {

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->auth($user_token);

        // -- End --
        return [
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                'user_status' => $user->user_status,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name,
                'user_timezone' => $user->user_timezone,

                
                'user_terms' => (array) call_user_func(function($user_id) {
                    $term_wrapper = new UserTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($user_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $user->id),

                /*
                'alerts_count' => $this->count_alerts($user->id)
                */
            ],
        ];
    }

    public function select(string $user_token, int $user_id) {

        $wrapper = new UserWrapper($this->em, $this->time);
        $user = $wrapper->auth($user_token);
        $member = $wrapper->select($user_id);

        return [
            'success' => 'true',
            'user' => [
                'id' => $member->id, 
                'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                'user_status' => $member->user_status,
                'user_name' => $member->user_name,
            ],
        ];

    }

    public function list(string $user_token, int $offset) {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);
        $users = $user_wrapper->list($user->id, $offset);

        // -- End --
        return [
            'success' => 'true',

            'users_limit' => $user_wrapper::LIST_LIMIT,

            /*
            'users_count' => (int) call_user_func(function($user_id, $term_key) {
                $term = $this->em->getRepository('\App\Entities\UserTerm')->findOneBy(['user_id' => $user_id, 'term_key' => $term_key]);
                return empty($term) ? 0 : $term->term_value;
            }, $user->id, 'relations_count'),
            */

            'users'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_email' => $n->user_email,
                'user_status' => $n->user_status,
                'user_name' => $n->user_name
            ], $users)
        ];
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

}
