<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="posts_terms")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class PostTerm
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
     * @Column(type="string", length="20") 
     * @var string
     */
    private $term_key;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $term_value;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Post", inversedBy="post_terms", fetch="EXTRA_LAZY")
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
            throw new AppException('create_date is empty', 1601);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1602);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1603);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1604);

        } elseif(empty($this->post_id)) {
            throw new AppException('post_id is empty', 1605);

        } elseif(!is_int($this->post_id)) {
            throw new AppException('post_id is incorrect', 1606);

        } elseif(empty($this->term_key)) {
            throw new AppException('term_key is empty', 1607);

        } elseif(!is_string($this->term_key) or mb_strlen($this->term_key) > 20) {
            throw new AppException('term_key is incorrect', 1608);

        } elseif(empty($this->term_value)) {
            throw new AppException('term_value is empty', 1609);

        } elseif(!is_string($this->term_value) or mb_strlen($this->term_value) > 255) {
            throw new AppException('term_value is incorrect', 1610);
        }
    }
}