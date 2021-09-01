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

class RoleRouter
{
    protected $em;

    const ROLE_USER_LIMIT = 50; // per user
    const ROLE_REPO_LIMIT = 10; // per repo
    const ROLE_LIST_LIMIT = 10;

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

    public function insert(string $user_token, string $user_email, int $repo_id, string $role_status) {

        $user_email = mb_strtolower($user_email);

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Filter: user roles limit --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user->id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::ROLE_USER_LIMIT) {
            throw new AppException('Role limit exceeded', 210);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- Filter: repo roles limit --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE repo_id = :repo_id");
        $stmt->bindValue(':repo_id', $repo->id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::ROLE_REPO_LIMIT) {
            throw new AppException('Role limit exceeded', 210);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('Role rights are not enough', 208);
        }

        // -- Member --
        $member = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($member)) {
            throw new AppException('User not found', 201);

        } elseif($member->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Member role --
        $member_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $member->id]);

        if(!empty($member_role)) {
            throw new AppException('Role is occupied', 209);
        }

        $member_role = new UserRole();
        $member_role->create_date = Flight::datetime();
        $member_role->update_date = new DateTime('1970-01-01 00:00:00');
        $member_role->user_id = $member->id;
        $member_role->repo_id = $repo->id;
        $member_role->role_status = $role_status;
        $member_role->user = $member;
        $member_role->repo = $repo;
        $this->em->persist($member_role);
        $this->em->flush();

        // -- Clear cache: user terms --
        foreach($member->user_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
            }
        }

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'user_role' => [
                'id' => $member_role->id
            ]
        ]);
    }

    public function update(string $user_token, int $user_id, int $repo_id, string $role_status) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('Role rights are not enough', 208);
        }

        // -- Member --
        $member = $this->em->find('App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('User not found', 201);

        } elseif($member->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Member role --
        $member_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $member->id]);

        if(empty($member_role)) {
            throw new AppException('Role not found', 207);

        } elseif($member_role->id == $user_role->id) {
            throw new AppException('Action prohibited', 102);
        }

        // -- Member role update --
        $member_role->update_date = Flight::datetime();
        $member_role->role_status = $role_status;
        $this->em->persist($member_role);
        $this->em->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

    public function delete(string $user_token, int $user_id, int $repo_id) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('Role rights are not enough', 208);
        }

        // -- Member --
        $member = $this->em->find('App\Entities\User', $user_id);

        if(empty($member)) {
            throw new AppException('User not found', 201);

        } elseif($member->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Member role --
        $member_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $member->id]);

        if(empty($member_role)) {
            throw new AppException('Role not found', 207);

        } elseif($member_role->id == $user_role->id) {
            throw new AppException('Action prohibited', 102);
        }

        // -- Delete alerts --
        $stmt = $this->em->getConnection()->prepare("DELETE FROM alerts WHERE user_id = :user_id AND repo_id = :repo_id");
        $stmt->bindValue('user_id', $member->id);
        $stmt->bindValue('repo_id', $repo->id);
        $stmt->execute();

        // -- Member role delete --
        $this->em->remove($member_role);
        $this->em->flush();

        // -- Clear cache: user terms --
        foreach($member->user_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
            }
        }

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

    public function list(string $user_token, int $repo_id, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);
        }

        // -- Roles --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('role.id')->from('App\Entities\UserRole', 'role')
            ->where($qb1->expr()->eq('role.repo_id', $repo->id))
            ->orderBy('role.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(self::ROLE_LIST_LIMIT);

        $roles = array_map(fn($n) => $this->em->find('App\Entities\UserRole', $n['id']), $qb1->getQuery()->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,
    
                'repo_terms' => call_user_func( 
                    function($repo_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $repo_terms), 
                            array_map(fn($n) => $n->term_value, $repo_terms));
                    }, $repo->repo_terms->toArray()),

                'user_role' => [
                    'id' => $user_role->id,
                    'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $user_role->user_id,
                    'repo_id' => $user_role->repo_id,
                    'role_status' => $user_role->role_status,
                ]
            ],

            'roles_limit' => self::ROLE_LIST_LIMIT,
            'roles_count' => (int) call_user_func( 
                function($terms, $key) {
                    $tmp = $terms->filter(function($el) use ($key) {
                        return $el->term_key == $key;
                    })->first();
                    return empty($tmp) ? 0 : $tmp->term_value;
                }, $repo->repo_terms, 'roles_count'),

            'roles'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'role_status' => $n->role_status,

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
                
            ], $roles)
        ]);
    }

}
