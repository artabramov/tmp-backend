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

class UserRoleWrapper
{
    protected $em;

    const ROLE_USER_LIMIT = 10; // per user
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
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Filter: user roles limit --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user->id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::ROLE_USER_LIMIT) {
            throw new AppException('user roles limit exceeded', 0);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('repo not found', 0);
        }

        // -- Filter: repo roles limit --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE repo_id = :repo_id");
        $stmt->bindValue(':repo_id', $repo->id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::ROLE_REPO_LIMIT) {
            throw new AppException('repo roles limit exceeded', 0);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('role not found', 0);

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('role_status must be admin', 0);
        }

        // -- Member --
        $member = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email]);

        if(empty($member)) {
            throw new AppException('user not found', 0);

        } elseif($member->user_status == 'trash') {
            throw new AppException('user is trash', 0);
        }

        // -- Member role --
        $member_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $member->id]);

        if(!empty($member_role)) {
            throw new AppException('role_status is occupied', 0);
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

        // -- Clear cache: user terms --
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

}
