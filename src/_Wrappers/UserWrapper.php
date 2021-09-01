<?php
namespace App\Wrappers;
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

class UserWrapper
{
    protected $em;

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

    // -- Count users registered in last 60 seconds --
    public function count_latest() {
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM users WHERE create_date > CURRENT_TIMESTAMP - INTERVAL '60 SECONDS'");
        $stmt->execute();
        return $stmt->fetchOne();
    }

    // -- Select related users --
    public function select_related(int $user_id, int $limit, int $offset) {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('relate_id', 'relate_id');

        $query = $this->em->createNativeQuery("SELECT relate_id FROM vw_users_relations WHERE user_id = :user_id LIMIT :limit OFFSET :offset", $rsm)
            ->setParameter('user_id', $user_id)
            ->setParameter('limit', $limit)
            ->setParameter('offset', $offset);

        return array_map(fn($n) => $this->em->find('App\Entities\User', $n['relate_id']), $query->getResult());
    }

    
}
