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

    const INSERT_LIMIT = 10; // roles limit for one user
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

        // -- Pre filter --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users_roles WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user_id, Type::INTEGER);
        $stmt->execute();
        $roles_count = $stmt->fetchOne();

        if($roles_count >= self::INSERT_LIMIT) {
            Halt::throw(1403); // role limit exceeded
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

    public function select(int $user_id, int $repo_id, string $role_status = '') {

        $role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['user_id' => $user_id, 'repo_id' => $repo_id]);

        if(empty($role)) {
            Halt::throw(1401); // role not found

        } elseif(!empty($role_status) and $role->role_status != 'admin') {
            Halt::throw(1402); // role action denied
        }

        return $role;
    }

    public function update(\App\Entities\UserRole $role, string $role_status) {

        $user->update_date = $this->time->datetime;
        $role->role_status = $role_status;
        $this->em->persist($role);
        $this->em->flush();
        return $role;
    }

    public function delete(\App\Entities\UserRole $role) {

        $this->em->remove($role);
        $this->em->flush();
    }

    // roles of the repo
    public function list(int $user_id) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('role.id')
            ->from('App\Entities\UserRole', 'role')
            ->where($qb1->expr()->eq('role.user_id', $user_id));

        $qb1_result = $qb1->getQuery()->getResult();
        $roles = array_map(fn($n) => $this->em->find('App\Entities\UserRole', $n['id']), $qb1_result);

        return $roles;
    }

}
