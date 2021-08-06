<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="posts_tags")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class PostTag
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
    private $post_id;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $tag_value;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Post", inversedBy="post_tags", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post;

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
    public function validate() {

        if(empty($this->create_date)) {
            throw new AppException('create_date is empty', 1701);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1702);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1703);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1704);

        } elseif(empty($this->post_id)) {
            throw new AppException('post_id is empty', 1705);

        } elseif(!is_int($this->post_id)) {
            throw new AppException('post_id is incorrect', 1706);

        } elseif(empty($this->tag_value)) {
            throw new AppException('tag_value is empty', 1707);

        } elseif(!is_string($this->tag_value) or mb_strlen($this->term_value) > 255) {
            throw new AppException('tag_value is incorrect', 1708);
        }
    }
}