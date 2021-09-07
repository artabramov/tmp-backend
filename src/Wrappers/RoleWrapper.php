<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\UserRole;
    

class RoleWrapper
{
    protected $em;
    protected $time;

    const INSERT_USER_LIMIT = 10; // roles limit for one user
    const INSERT_REPO_LIMIT = 10; // roles limit for one repo
    const LIST_LIMIT = 5; // number of results in list

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

    public function insert(int $user_id, int $repo_id, string $role_status) {

        // -- Pre filter: user roles limit --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user_id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::INSERT_USER_LIMIT) {
            Halt::throw(1403); // role limit exceeded
        }

        // -- Pre filter: repo roles limit --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE repo_id = :repo_id");
        $stmt->bindValue(':repo_id', $repo_id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::INSERT_REPO_LIMIT) {
            Halt::throw(1403); // role limit exceeded
        }

        // -- Check is role already exists --
        $role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['user_id' => $user_id, 'repo_id' => $repo_id]);

        if(!empty($role)) {
            Halt::throw(1402); // role action denied
        }

        $role = new UserRole();
        $role->create_date = $this->time->datetime;
        $role->update_date = new DateTime('1970-01-01 00:00:00');
        $role->user_id = $user_id;
        $role->repo_id = $repo_id;
        $role->role_status = $role_status;

        $this->em->persist($role);
        $this->em->flush();
        return $role;
    }

    public function select(int $user_id, int $repo_id, array $role_statuses = []) {

        $role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['user_id' => $user_id, 'repo_id' => $repo_id]);

        if(empty($role)) {
            Halt::throw(1401); // role not found

        } elseif(!empty($role_status) and !in_array($role->role_status, $role_statuses)) {
            Halt::throw(1402); // role action denied
        }

        return $role;
    }

    public function update(\App\Entities\UserRole $role, \App\Entities\Repo $repo, string $role_status) {

        if($repo->user_id == $role->user_id) {
            Halt::throw(1402); // role action denied
        }

        $role->update_date = $this->time->datetime;
        $role->role_status = $role_status;
        $this->em->persist($role);
        $this->em->flush();
        return $role;
    }

    public function delete(\App\Entities\UserRole $role, \App\Entities\Repo $repo) {

        if($repo->user_id == $role->user_id) {
            Halt::throw(1402); // role action denied
        }

        $this->em->remove($role);
        $this->em->flush();
    }

    // roles of the repo
    public function list(int $repo_id, int $offset) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('role.id')->from('App\Entities\UserRole', 'role')
            ->where($qb1->expr()->eq('role.repo_id', $repo_id))
            ->orderBy('role.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIST_LIMIT);

        $roles = array_map(fn($n) => $this->em->find('App\Entities\UserRole', $n['id']), $qb1->getQuery()->getResult());

        return $roles;
    }

}
