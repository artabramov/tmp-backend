<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="premiums")
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
    protected $trash_date;

    /**
     * @Column(type="integer", nullable="true")
     * @var int
     */
    private $user_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('hold', 'trash')") 
     * @var string
     */
    private $premium_status;

    /**
     * @Column(type="string", length="20")
     * @var int
     */
    private $premium_key;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $premium_size;

    /** 
     * @Column(type="string", length="20")
     * @var string
     */
    private $premium_interval;

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
            throw new AppException('create_date is empty', 2201);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 2202);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 2203);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 2204);

        } elseif(empty($this->trash_date)) {
            throw new AppException('trash_date is empty', 2205);

        } elseif(!$this->trash_date  instanceof \DateTime) {
            throw new AppException('trash_date is incorrect', 2206);

        } elseif(!empty($this->user_id) and !is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 2207);

        } elseif(empty($this->premium_status)) {
            throw new AppException('premium_status is empty', 2208);

        } elseif(!in_array($this->premium_status, ['hold', 'trash'])) {
            throw new AppException('premium_status is incorrect', 2209);

        } elseif(empty($this->premium_key)) {
            throw new AppException('premium_key is empty', 2210);

        } elseif(!is_string($this->premium_key) or mb_strlen($this->premium_key) < 2 or mb_strlen($this->premium_key) > 20) {
            throw new AppException('premium_key is incorrect', 2211);

        } elseif(empty($this->premium_size)) {
            throw new AppException('premium_size is empty', 2212);

        } elseif(!is_int($this->premium_size)) {
            throw new AppException('premium_size is incorrect', 2213);

        } elseif(empty($this->premium_interval)) {
            throw new AppException('premium_interval is empty', 2214);

        } elseif(!is_string($this->premium_interval) or mb_strlen($this->premium_interval) < 2 or mb_strlen($this->premium_interval) > 20) {
            throw new AppException('premium_interval is incorrect', 2215);
        }
    }
}
