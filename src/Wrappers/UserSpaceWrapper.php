<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\UserSpace;
    

class UserSpaceWrapper
{
    protected $em;
    protected $time;

    const DEFAULT_SIZE = 100;

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

    // select user space
    public function select(int $user_id) {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->em->createNativeQuery("SELECT id FROM users_spaces WHERE user_id = :user_id AND expires_date > NOW() ORDER BY space_size DESC LIMIT 1", $rsm)
            ->setParameter('user_id', $user_id);

        $query_result = $query->getResult();
        $space = !empty($query_result) ? $this->em->find('App\Entities\UserSpace', $query_result[0]['id']) : null;
        return $space;
    }


}
