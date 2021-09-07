<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\Post;
    

class PostWrapper
{
    protected $em;
    protected $time;

    const INSERT_LIMIT = 1024; // posts limit for one user
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

    public function insert(int $user_id, int $repo_id, string $post_status, string $post_title) {

        // -- Filter: posts number --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM posts WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user_id, Type::INTEGER);
        $stmt->execute();
        $posts_count = $stmt->fetchOne();

        if($posts_count >= self::INSERT_LIMIT) {
            Halt::throw(1703); // post limit exceeded
        }

        // -- Post --
        $post = new Post();
        $post->create_date = $this->time->datetime;
        $post->update_date = new DateTime('1970-01-01 00:00:00');
        $post->user_id = $user_id;
        $post->repo_id = $repo_id;
        $post->post_status = $post_status;
        $post->post_title = $post_title;

        $this->em->persist($post);
        $this->em->flush();
        return $post;
    }

    public function select(int $repo_id) {

        $repo = $this->em->getRepository('\App\Entities\Repo')->find($repo_id);

        if(empty($repo)) {
            Halt::throw(1501); // repo not found
        }

        return $repo;
    }

    public function update(\App\Entities\Repo $repo, string $repo_name) {

        $repo->update_date = $this->time->datetime;
        $repo->repo_name = $repo_name;
        $this->em->persist($repo);
        $this->em->flush();
        return $repo;
    }

    public function delete(\App\Entities\Repo $repo) {

        $this->em->remove($repo);
        $this->em->flush();
    }

    // repos of the user
    public function list(int $user_id, int $offset) {

        $qb2 = $this->em->createQueryBuilder();

        $qb2->select('role.repo_id')
            ->from('App\Entities\UserRole', 'role')
            ->where($qb2->expr()->eq('role.user_id', $user_id));

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select(['repo.id'])
            ->from('App\Entities\Repo', 'repo')
            ->where($qb1->expr()->in('repo.id', $qb2->getDQL()))
            ->orderBy('repo.repo_name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIST_LIMIT);

        $repos = array_map(fn($n) => $this->em->find('App\Entities\Repo', $n['id']), $qb1->getQuery()->getResult());
        return $repos;
    }

}
