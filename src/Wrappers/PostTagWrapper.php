<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\PostTag;
    

class PostTagWrapper
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

    // insert tags
    public function insert(int $post_id, array $tags_values) {

        foreach($tags_values as $tag_value) {
            $post_tag = $this->em->getRepository('\App\Entities\PostTag')->findOneBy(['post_id' => $post_id, 'tag_value' => $tag_value]);

            if(empty($post_tag)) {
                $post_tag = new PostTag();
                $post_tag->create_date = $this->time->datetime;
                $post_tag->update_date = new DateTime('1970-01-01 00:00:00');
                $post_tag->post_id = $post_id;
                $post_tag->tag_value = $tag_value;
    
                $this->em->persist($post_tag);
                $this->em->flush();
            }            
        }
    }

    // update all tags of the post
    public function update(int $post_id, array $tags_values) {
        $post_tags = $this->list($post_id);
        foreach($post_tags as $post_tag) {
            $this->em->remove($post_tag);
            $this->em->flush();
        }
        $this->insert($post_id, $tags_values);
    }

    public function list(int $post_id) {

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('tag.id')
            ->from('App\Entities\PostTag', 'tag')
            ->where($qb1->expr()->eq('tag.post_id', $post_id));

        $qb1_result = $qb1->getQuery()->getResult();
        $tags = array_map(fn($n) => $this->em->find('App\Entities\PostTag', $n['id']), $qb1_result);

        return $tags;
    }


}
