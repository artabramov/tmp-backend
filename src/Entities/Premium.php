<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_premiums")
 */
class Premium
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
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $expire_date;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $premium_limit;

    public function __construct() {
        $this->create_date = new \DateTime('now');
        $this->update_date = new \DateTime('1970-01-01 00:00:00');
        $this->expire_date = new \DateTime('1970-01-01 00:00:00');
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
            throw new AppException('Premium error: user_id is empty.');

        } elseif(!is_numeric($this->user_id)) {
            throw new AppException('Premium error: user_id is not numeric.');

        } elseif(empty($this->premium_limit)) {
            throw new AppException('Premium error: premium_limit is empty.');

        } elseif(!is_numeric($this->premium_limit)) {
            throw new AppException('Premium error: premium_limit is not numeric.');
        }
    }
}
