<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="comments")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Comment
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
    private $post_id;

    /** 
     * @Column(type="string") 
     * @var string
     */
    private $comment_content;

    public function __construct() {
        $this->comment_uploads = new \Doctrine\Common\Collections\ArrayCollection();
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
            Halt::throw(2004); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(2005); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(2006); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(2007); // update_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(2008); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(2009); // user_id is incorrect

        } elseif(empty($this->post_id)) {
            Halt::throw(2010); // post_id is empty

        } elseif(!is_int($this->post_id)) {
            Halt::throw(2011); // post_id is incorrect

        } elseif(empty($this->comment_content)) {
            Halt::throw(2012); // comment_content is empty

        } elseif(!is_string($this->comment_content) or mb_strlen($this->comment_content) < 2 or mb_strlen($this->comment_content) > 65535) {
            Halt::throw(2013); // comment_content is incorrect
        }
    }
    
}
