<?php
namespace App\Entities;
use \App\Services\Halt;

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
            Halt::throw(1904); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1905); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1906); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1907); // update_date is incorrect

        } elseif(empty($this->post_id)) {
            Halt::throw(1908); // post_id is empty

        } elseif(!is_int($this->post_id)) {
            Halt::throw(1909); // post_id is incorrect

        } elseif(empty($this->tag_value)) {
            Halt::throw(1910); // tag_value is empty

        } elseif(!is_string($this->tag_value) or mb_strlen($this->tag_value) < 2 or mb_strlen($this->term_value) > 255) {
            Halt::throw(1911); // tag_value is incorrect
        }
    }
}
