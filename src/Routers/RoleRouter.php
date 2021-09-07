<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Services\Email,
    \App\Wrappers\UserWrapper,
    \App\Wrappers\UserTermWrapper,
    \App\Wrappers\RepoWrapper,
    \App\Wrappers\RepoTermWrapper,
    \App\Wrappers\RoleWrapper;

class RoleRouter
{
    protected $em;
    protected $time;

    const ROLE_USER_LIMIT = 50; // per user
    const ROLE_REPO_LIMIT = 10; // per repo
    const ROLE_LIST_LIMIT = 10;

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

    public function insert(string $user_token, string $user_email, int $repo_id, string $role_status) {

        $user_email = mb_strtolower($user_email);

        // user & mate
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);
        $mate = $user_wrapper->byemail($user_email);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        // user & mate roles
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin']);
        $mate_role = $role_wrapper->insert($mate->id, $repo->id, $role_status);

        // user cache
        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($mate->id, 'roles_count');
        $user_term_wrapper->evict($mate->id, 'relations_count');

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, 'roles_count');

        return [
            'success' => 'true',
            'user_role' => [
                'id' => $mate_role->id
            ]
        ];
    }

    public function update(string $user_token, int $user_id, int $repo_id, string $role_status) {

        // user & mate
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);
        $mate = $user_wrapper->select($user_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        // user & mate roles
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin']);
        $mate_role = $role_wrapper->select($mate->id, $repo->id);

        // update mate role
        $mate_role = $role_wrapper->update($mate_role, $repo, $role_status);

        return [ 
            'success' => 'true'
        ];
    }

    public function delete(string $user_token, int $user_id, int $repo_id) {

        // user & mate
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);
        $mate = $user_wrapper->select($user_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        // user & mate roles
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin']);
        $mate_role = $role_wrapper->select($mate->id, $repo->id);

        // delete mate role
        $role_wrapper->delete($mate_role, $repo);

        // user cache
        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($mate->id, 'roles_count');
        $user_term_wrapper->evict($mate->id, 'relations_count');

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, 'roles_count');

        /*
        // -- Delete alerts --
        $stmt = $this->em->getConnection()->prepare("DELETE FROM alerts WHERE user_id = :user_id AND repo_id = :repo_id");
        $stmt->bindValue('user_id', $member->id);
        $stmt->bindValue('repo_id', $repo->id);
        $stmt->execute();
        */

        return [ 
            'success' => 'true'
        ];
    }

    public function list(string $user_token, int $repo_id, int $offset) {

        // user
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        // user & mate roles
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id);

        // roles list
        $roles = $role_wrapper->list($repo->id, $offset);

        return [
            'success' => 'true',

            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,
    
                'repo_terms' => (array) call_user_func(function($repo_id) {
                    $term_wrapper = new RepoTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($repo_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $repo->id),

                'user_role' => [
                    'id' => $user_role->id,
                    'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $user_role->user_id,
                    'repo_id' => $user_role->repo_id,
                    'role_status' => $user_role->role_status,
                ]
            ],

            'roles_limit' => $role_wrapper::LIST_LIMIT,

            /*
            
            'roles_count' => (int) call_user_func( 
                function($terms, $key) {
                    $tmp = $terms->filter(function($el) use ($key) {
                        return $el->term_key == $key;
                    })->first();
                    return empty($tmp) ? 0 : $tmp->term_value;
                }, $repo->repo_terms, 'roles_count'),
            */

            'roles'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'role_status' => $n->role_status,

                /*
                'user'=> call_user_func(
                    function($user_id) {
                        $member = $this->em->find('App\Entities\User', $user_id);
                        return [
                            'id' => $member->id,
                            'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                            'user_status' => $member->user_status,
                            'user_name' => $member->user_name
                        ];
                    }, $n->user_id
                )
                */
                
            ], $roles)
        ];
    }

}
