<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_volumes")
 * @Cache("NONSTRICT_READ_WRITE")
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
            throw new AppException('create_date is empty', 1301);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1302);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1303);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1304);

        } elseif(empty($this->expires_date)) {
            throw new AppException('expires_date is empty', 1305);

        } elseif(!$this->expires_date  instanceof \DateTime) {
            throw new AppException('expires_date is incorrect', 1306);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 1307);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 1308);

        } elseif(empty($this->volume_size)) {
            throw new AppException('volume_size is empty', 1309);

        } elseif(!is_int($this->volume_size)) {
            throw new AppException('volume_size is incorrect', 1310);
        }
    }
}
