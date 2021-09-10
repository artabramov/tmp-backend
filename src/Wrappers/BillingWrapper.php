<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\UserSpace;
    

class BillingWrapper
{
    protected $em;
    protected $time;

    const SIZE_LIMIT = 1000000; // default space size

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

    public function select(string $billing_code) {

        $billing = $this->em->getRepository('\App\Entities\Billing')->findOneBy(['billing_code' => $billing_code]);

        if(empty($billing)) {
            Halt::throw(1301); // billing not found
        }

        return $billing;
    }

    public function approve(\App\Entities\Billing $billing, int $user_id) {

        $billing->update_date = $this->time->datetime;
        $billing->expires_date = $this->time->datetime;
        $billing->expires_date->add(new DateInterval($billing->space_interval));
        $billing->billing_status = 'approved';
        $billing->user_id = $user_id;
        $this->em->persist($billing);
        $this->em->flush();
        return $billing;
    }

    /*
    // TODO: is user space filled up
    public function crowded(int $user_id) {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->em->createNativeQuery("SELECT id FROM users_spaces WHERE user_id = :user_id AND expires_date > NOW() ORDER BY space_size DESC LIMIT 1", $rsm)
            ->setParameter('user_id', $user_id);

        $query_result = $query->getResult();
        $space = !empty($query_result) ? $this->em->find('App\Entities\UserSpace', $query_result[0]['id']) : null;
        return $space;
    }
    */


}
