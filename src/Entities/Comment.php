<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="posts_comments")
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

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Upload", mappedBy="comment", fetch="EXTRA_LAZY")
     * @JoinColumn(name="comment_id", referencedColumnName="id")
     */
    private $comment_uploads;

    public function __construct() {
        $this->comment_uploads = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __set( $key, $value ) {
        if( property_exists( $this, $key )) {
            $this->$key = $value;
        }
    }

    public function __get( $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return null;
    }

    public function __isset( $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    /** 
     * @PrePersist
     * @PreUpdate
     */
    public function validate() {

        if(empty($this->user_id)) {
            throw new AppException('Comment error: user_id is empty.');

        } elseif(!is_numeric($this->user_id)) {
            throw new AppException('Comment error: user_id is not numeric.');

        } elseif(empty($this->post_id)) {
            throw new AppException('Comment error: post_id is empty.');

        } elseif(!is_numeric($this->post_id)) {
            throw new AppException('Comment error: post_id is not numeric.');

        } elseif(empty($this->comment_content)) {
            throw new AppException('Comment error: comment_content is empty.');

        } elseif(mb_strlen($this->comment_content) < 2) {
            throw new AppException('Comment error: comment_content is too short.');

        } elseif(mb_strlen($this->comment_content) > 65535) {
            throw new AppException('Comment error: comment_content is too long.');
        }
    }
    
}
