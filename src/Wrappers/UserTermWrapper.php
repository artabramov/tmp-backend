<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\UserTerm;
    

class UserTermWrapper
{
    protected $em;
    protected $time;

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

    public function insert(int $user_id, string $term_key, mixed $term_value) {

        $term = new UserTerm();
        $term->create_date = $this->time->datetime;
        $term->update_date = new DateTime('1970-01-01 00:00:00');
        $term->user_id = $user_id;
        $term->term_key = $term_key;
        $term->term_value = $term_value;

        $this->em->persist($term);
        $this->em->flush();

        return $term;
    }

    /**
     * Select all terms of the user.
     */
    public function list(int $user_id) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('term.id')
            ->from('App\Entities\UserTerm', 'term')
            ->where($qb1->expr()->eq('term.user_id', $user_id));

        $qb1_result = $qb1->getQuery()->getResult();
        $terms = array_map(fn($n) => $this->em->find('App\Entities\UserTerm', $n['id']), $qb1_result);

        return $terms;
    }

    /*
    public function select(array $args) {

        $user = $this->em->getRepository('\App\Entities\User')->findOneBy($args);

        if(empty($user)) {
            Halt::throw(1101); // user not found
        }

        return $user;
    }

    public function update($user, array $data) {

        if($user->user_status == 'trash') {
            Halt::throw(1112); // user_status is trash
        }

        foreach($data as $key => $value) {
            $user->$key = $value;
        }

        $user->update_date = $this->time->datetime;
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
    */



}
