<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="alerts")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Alert
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
     * @Column(type="integer")
     * @var int
     */
    private $post_id;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $comment_id;

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
            Halt::throw(2204); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(2205); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(2206); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(2207); // update_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(2208); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(2209); // user_id is incorrect

        } elseif(empty($this->repo_id)) {
            Halt::throw(2210); // repo_id is empty

        } elseif(!is_int($this->repo_id)) {
            Halt::throw(2211); // repo_id is incorrect

        } elseif(empty($this->post_id)) {
            Halt::throw(2212); // post_id is empty

        } elseif(!is_int($this->post_id)) {
            Halt::throw(2213); // post_id is incorrect

        } elseif(empty($this->comment_id)) {
            Halt::throw(2214); // comment_id is empty

        } elseif(!is_int($this->comment_id)) {
            Halt::throw(2215); // comment_id is incorrect
        }
    }
}
