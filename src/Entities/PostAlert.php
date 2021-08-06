<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="posts_alerts")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class PostAlert
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
     * @Column(type="integer")
     * @var int
     */
    private $alerts_count;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Post", inversedBy="post_alerts", fetch="EXTRA_LAZY")
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
            throw new AppException('create_date is empty', 1201);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1202);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1203);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1204);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 1205);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 1206);

        } elseif(empty($this->post_id)) {
            throw new AppException('post_id is empty', 1207);

        } elseif(!is_int($this->post_id)) {
            throw new AppException('post_id is incorrect', 1208);

        } elseif(empty($this->alerts_count)) {
            throw new AppException('alerts_count is empty', 1209);

        } elseif(!is_int($this->alerts_count)) {
            throw new AppException('alerts_count is incorrect', 1210);
        }
    }
}
