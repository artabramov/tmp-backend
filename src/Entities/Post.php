<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="posts")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Post
{
    protected $error;

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
     * @Cache("NONSTRICT_READ_WRITE")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @Cache("NONSTRICT_READ_WRITE")
     * @var int
     */
    private $hub_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('todo', 'doing', 'done', 'trash')") 
     * @var string
     */
    private $post_status;

    /** 
     * @Column(type="string", length="255") 
     * @var string
     */
    private $post_title;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Postmeta", mappedBy="post", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post_meta;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Tag", mappedBy="post", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post_tags;

    public function __construct() {
        $this->error = '';
        $this->create_date = new \DateTime('now');
        $this->update_date = new \DateTime('1970-01-01 00:00:00');
        $this->remind_date = new \DateTime('1970-01-01 00:00:00');
        $this->user_meta = new \Doctrine\Common\Collections\ArrayCollection();
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
            throw new AppException('Post error: user_id is empty.');

        } elseif(!is_numeric($this->user_id)) {
            throw new AppException('Post error: user_id is not numeric.');

        } elseif(empty($this->hub_id)) {
            throw new AppException('Post error: hub_id is empty.');

        } elseif(!is_numeric($this->hub_id)) {
            throw new AppException('Post error: hub_id is not numeric.');

        } elseif(empty($this->post_status)) {
            throw new AppException('Post error: post_status is empty.');

        } elseif(!in_array($this->post_status, ['todo', 'doing', 'done', 'trash'])) {
            throw new AppException('Post error: post_status is incorrect.');

        } elseif(empty($this->post_title)) {
            throw new AppException('Post error: post_title is empty.');

        } elseif(mb_strlen($this->post_title) < 4) {
            throw new AppException('Post error: post_title is too short.');

        } elseif(mb_strlen($this->post_title) > 255) {
            throw new AppException('Post error: post_title is too long.');
        }
    }
    
}
