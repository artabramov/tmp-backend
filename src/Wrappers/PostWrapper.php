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

    public function update(\App\Entities\Post $post, \App\Entities\UserRole $role, string $post_status, string $post_title) {

        if($post->user_id != $role->user_id and $role->role_status != 'admin') {
            Halt::throw(1702); // post action denied
        }

        $post->update_date = $this->time->datetime;
        $post->post_status = $post_status;
        $post->post_title = $post_title;
        $this->em->persist($post);
        $this->em->flush();
        return $post;
    }

    public function select(int $post_id) {

        $post = $this->em->getRepository('\App\Entities\Post')->find($post_id);

        if(empty($post)) {
            Halt::throw(1701); // post not found
        }

        return $post;
    }

    public function delete(\App\Entities\Post $post, \App\Entities\UserRole $role) {

        if($post->user_id != $role->user_id and $role->role_status != 'admin') {
            Halt::throw(1702); // post action denied
        }

        $this->em->remove($post);
        $this->em->flush();
    }

    public function list(int $repo_id, string $post_status, int $offset) {

        $qb1 = $this->em->createQueryBuilder();

        $qb1->select('post.id')->from('App\Entities\Post', 'post')
            ->where($qb1->expr()->eq('post.repo_id', $repo_id))
            ->andWhere($qb1->expr()->eq('post.post_status', $this->em->getConnection()->quote($post_status, \Doctrine\DBAL\ParameterType::STRING)))
            ->orderBy('post.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIST_LIMIT);

        $posts = array_map(fn($n) => $this->em->find('App\Entities\Post', $n['id']), $qb1->getQuery()->getResult());
        return $posts;
    }

}
