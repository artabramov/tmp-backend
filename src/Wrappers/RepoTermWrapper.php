<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\RepoTerm;
    

class RepoTermWrapper
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

    // evict term from cache
    public function evict(int $repo_id, string $term_key) {

        $term = $this->em->getRepository('\App\Entities\RepoTerm')->findOneBy(['repo_id' => $repo_id, 'term_key' => $term_key]);

        if(!empty($term)) {
            $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
        }
    }

    // insert term
    public function insert(int $repo_id, string $term_key, mixed $term_value) {

        $term = new RepoTerm();
        $term->create_date = $this->time->datetime;
        $term->update_date = new DateTime('1970-01-01 00:00:00');
        $term->repo_id = $repo_id;
        $term->term_key = $term_key;
        $term->term_value = $term_value;

        $this->em->persist($term);
        $this->em->flush();
        return $term;
    }

    // select term
    public function select(int $repo_id, string $term_key) {

        $term = $this->em->getRepository('\App\Entities\RepoTerm')->findOneBy(['repo_id' => $repo_id, 'term_key' => $term_key]);
        return $term;
    }

    // update term
    public function update(\App\Entities\RepoTerm $term, string $term_value) {

        $term->update_date = $this->time->datetime;
        $term->term_value = $term_value;
        $this->em->persist($term);
        $this->em->flush();
        return $term;
    }

    // delete term
    public function delete(\App\Entities\RepoTerm $term) {

        $this->em->remove($term);
        $this->em->flush();
    }

    // select all terms
    public function list(int $repo_id) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('term.id')
            ->from('App\Entities\RepoTerm', 'term')
            ->where($qb1->expr()->eq('term.repo_id', $repo_id));

        $qb1_result = $qb1->getQuery()->getResult();
        $terms = array_map(fn($n) => $this->em->find('App\Entities\RepoTerm', $n['id']), $qb1_result);

        return $terms;
    }

}
