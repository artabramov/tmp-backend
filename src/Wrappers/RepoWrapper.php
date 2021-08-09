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

class RepoWrapper
{
    protected $em;

    const REPO_CREATE_LIMIT = 20; // maximum repo creates per 1 minute
    const REPO_LIST_LIMIT = 10;

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

    public function create(string $user_token, string $repo_name) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        $user->auth_date = Flight::datetime();
        $this->em->persist($user);
        $this->em->flush();

        // -- Filter: repo insert --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM repos WHERE user_id = :user_id AND create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->bindValue(':user_id', $user->id, Type::INTEGER);
        $stmt->execute();
        $users_count = $stmt->fetchOne();

        if($users_count > self::USER_REGISTER_LIMIT) {
            throw new AppException('wait for 60 seconds', 0);
        }

        // -- Insert repo --
        $repo = new Repo();
        $repo->create_date = Flight::datetime();
        $repo->update_date = new DateTime('1970-01-01 00:00:00');
        $repo->user_id = $user->id;
        $repo->repo_name = $repo_name;
        $this->em->persist($repo);
        $this->em->flush();

        // -- Insert user role --
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

        // -- Clear cache: user terms --
        if($this->em->getCache()->containsCollection('\App\Entities\User', 'user_terms', $user->id)) {
            $this->em->getCache()->evictCollection('\App\Entities\User', 'user_terms', $user->id);
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'repo' => [
                'id' => $repo->id
            ]
        ]);
    }

}
