<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\Comment;

class CommentWrapper
{
    protected $em;
    protected $time;

    const INSERT_LIMIT = 1024; // comments limit for one user
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

    public function insert(int $user_id, int $post_id, string $comment_content) {

        // -- Filter: comments number --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM comments WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user_id, Type::INTEGER);
        $stmt->execute();
        $comments_count = $stmt->fetchOne();

        if($comments_count >= self::INSERT_LIMIT) {
            Halt::throw(2003); // post limit exceeded
        }

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = $this->time->datetime;
        $comment->update_date = new DateTime('1970-01-01 00:00:00');
        $comment->user_id = $user_id;
        $comment->post_id = $post_id;
        $comment->comment_content = $comment_content;

        $this->em->persist($comment);
        $this->em->flush();
        return $comment;
    }

    public function select(int $comment_id) {

        $comment = $this->em->getRepository('\App\Entities\Comment')->find($comment_id);

        if(empty($comment)) {
            Halt::throw(2001); // comment not found
        }

        return $comment;
    }

    public function delete(\App\Entities\Comment $comment, \App\Entities\UserRole $role) {

        if($comment->user_id != $role->user_id and $role->role_status != 'admin') {
            Halt::throw(2002); // comment action denied
        }

        $this->em->remove($comment);
        $this->em->flush();
    }

    public function update(\App\Entities\Comment $comment, \App\Entities\UserRole $role, string $comment_content) {

        if($comment->user_id != $role->user_id and $role->role_status != 'admin') {
            Halt::throw(2002); // comment action denied
        }

        $comment->update_date = $this->time->datetime;
        $comment->comment_content = $comment_content;
        $this->em->persist($comment);
        $this->em->flush();
        return $comment;
    }

    public function list(int $post_id, int $offset) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('comment.id')->from('App\Entities\Comment', 'comment')
            ->where($qb1->expr()->eq('comment.post_id', $post_id))
            ->orderBy('comment.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIST_LIMIT);
        $comments = array_map(fn($n) => $this->em->find('App\Entities\Comment', $n['id']), $qb1->getQuery()->getResult());
        return $comments;
    }


}
