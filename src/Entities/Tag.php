<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="posts_tags")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Tag
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
     * @Cache("NONSTRICT_READ_WRITE")
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

    public function __construct() {
        $this->create_date = new \DateTime('now');
        $this->update_date = new \DateTime('1970-01-01 00:00:00');
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

        if(empty($this->post_id)) {
            throw new AppException('Tag error: post_id is empty.');

        } elseif(!is_numeric($this->post_id)) {
            throw new AppException('Tag error: post_id is not numeric.');

        } elseif(empty($this->tag_value)) {
            throw new AppException('Tag error: tag_value is empty.');

        } elseif(mb_strlen($this->tag_value) > 255) {
            throw new AppException('Tag error: tag_value is too long.');
        }
    }
}
