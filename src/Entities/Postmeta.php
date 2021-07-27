<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="posts_meta")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Postmeta
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
    private $meta_key;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $meta_value;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Post", inversedBy="post_meta", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post;

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
            throw new AppException('Meta error: post_id is empty.');

        } elseif(!is_numeric($this->post_id)) {
            throw new AppException('Meta error: post_id is not numeric.');

        } elseif(empty($this->meta_key)) {
            throw new AppException('Meta error: meta_key is empty.');

        } elseif(mb_strlen($this->meta_key) > 20) {
            throw new AppException('Meta error: meta_key is too long.');

        //} elseif(empty($this->meta_value)) {
        //    throw new AppException('Meta error: meta_value is empty.');

        } elseif(mb_strlen($this->meta_value) > 255) {
            throw new AppException('Meta error: meta_value is too long.');
        }
    }
}
