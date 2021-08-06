<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_volumes")
 */
class UserVolume
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
    protected $expires_date;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $volume_size;

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
            throw new AppException('create_date is empty', 2101);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 2102);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 2103);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 2104);

        } elseif(empty($this->expires_date)) {
            throw new AppException('expires_date is empty', 2105);

        } elseif(!$this->expires_date  instanceof \DateTime) {
            throw new AppException('expires_date is incorrect', 2106);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 2107);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 2108);

        } elseif(empty($this->volume_size)) {
            throw new AppException('volume_size is empty', 2109);

        } elseif(!is_int($this->volume_size)) {
            throw new AppException('volume_size is incorrect', 2110);
        }
    }
}
