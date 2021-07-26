<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_alerts")
 */
class Alert
{
    /**
     * @Id
     * @Column(type="integer")
     * @Cache("NONSTRICT_READ_WRITE")
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
    private $post_id;

    /**
     * @Column(type="integer")
     * @Cache("NONSTRICT_READ_WRITE")
     * @var int
     */
    private $alerts_count;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\User", inversedBy="user_alerts", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
            throw new AppException('Alert error: user_id is empty.');

        } elseif(!is_numeric($this->user_id)) {
            throw new AppException('Alert error: user_id is not numeric.');

        } elseif(empty($this->post_id)) {
            throw new AppException('Alert error: post_id is empty.');

        } elseif(!is_numeric($this->post_id)) {
            throw new AppException('Alert error: post_id is not numeric.');

        } elseif(empty($this->alerts_count)) {
            throw new AppException('Alert error: alerts_count is empty.');

        } elseif(!is_numeric($this->alerts_count)) {
            throw new AppException('Alert error: alerts_count is not numeric.');
        }
    }
}
