<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="posts")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Post
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $create_date;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $update_date;


    /**
     * @Column(type="integer")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $repo_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('todo', 'doing', 'done')") 
     * @var string
     */
    private $post_status;

    /** 
     * @Column(type="string", length="255") 
     * @var string
     */
    private $post_title;

    public function __construct() {
        $this->post_terms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->post_tags = new \Doctrine\Common\Collections\ArrayCollection();
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

    /** 
     * @PrePersist
     * @PreUpdate
     */
    public function pre() {

        if(empty($this->create_date)) {
            Halt::throw(1704); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1705); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1706); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1707); // update_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(1708); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(1709); // user_id is incorrect

        } elseif(empty($this->repo_id)) {
            Halt::throw(1710); // repo_id is empty

        } elseif(!is_int($this->repo_id)) {
            Halt::throw(1711); // repo_id is incorrect

        } elseif(empty($this->post_status)) {
            Halt::throw(1712); // post_status is empty

        } elseif(!in_array($this->post_status, ['todo', 'doing', 'done'])) {
            Halt::throw(1713); // post_status is incorrect

        } elseif(empty($this->post_title)) {
            Halt::throw(1714); // post_title is empty

        } elseif(!is_string($this->post_title) or mb_strlen($this->post_title) < 2 or mb_strlen($this->post_title) > 255) {
            Halt::throw(1715); // post_title is incorrect
        }
    }
}
