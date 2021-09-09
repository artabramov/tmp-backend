<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\Alert;
    

class AlertWrapper
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

    public function ofuser(int $user_id) {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $query = $this->em->createNativeQuery("SELECT alerts_count FROM vw_users_alerts WHERE user_id = :user_id LIMIT 1", $rsm)
            ->setParameter('user_id', $user_id);

        $query_result = $query->getResult();
        return !empty($query_result) ? $query_result[0]['alerts_count'] : null;
    }

    public function ofrepo(int $user_id, int $repo_id) {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $query = $this->em->createNativeQuery("SELECT alerts_count FROM vw_repos_alerts WHERE user_id = :user_id AND repo_id = :repo_id LIMIT 1", $rsm)
        ->setParameter('user_id', $user_id)
        ->setParameter('repo_id', $repo_id);

        $query_result = $query->getResult();
        return !empty($query_result) ? $query_result[0]['alerts_count'] : null;
    }

    public function ofpost(int $user_id, int $post_id) {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $query = $this->em->createNativeQuery("SELECT alerts_count FROM vw_posts_alerts WHERE user_id = :user_id AND post_id = :post_id LIMIT 1", $rsm)
        ->setParameter('user_id', $user_id)
        ->setParameter('post_id', $post_id);

        $query_result = $query->getResult();
        return !empty($query_result) ? $query_result[0]['alerts_count'] : null;
    }

    // delete all alerts of the post
    public function delete(int $user_id, int $post_id) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('alert.id')
            ->from('App\Entities\Alert', 'alert')
            ->where($qb1->expr()->eq('alert.user_id', $user_id))
            ->andWhere($qb1->expr()->eq('alert.post_id', $post_id));

        $qb1_result = $qb1->getQuery()->getResult();
        $alerts = array_map(fn($n) => $this->em->find('App\Entities\Alert', $n['id']), $qb1_result);

        foreach($alerts as $alert) {
            $this->em->remove($alert);
            $this->em->flush();
        }

    }

}
