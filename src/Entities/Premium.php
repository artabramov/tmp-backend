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
     * @Column(type="string", length="40", unique="true")
     * @var int
     */
    private $premium_code;

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

    /**
     * @Column(type="string", length="20")
     * @var int
     */
    private $referrer_key;

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
            throw new AppException('Create date is empty', 301);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('Create date is incorrect', 302);

        } elseif(empty($this->update_date)) {
            throw new AppException('Update date is empty', 303);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('Update date is incorrect', 304);

        } elseif(empty($this->trash_date)) {
            throw new AppException('Trash date is empty', 305);

        } elseif(!$this->trash_date  instanceof \DateTime) {
            throw new AppException('Trash date is incorrect', 306);

        } elseif(!empty($this->user_id) and !is_int($this->user_id)) {
            throw new AppException('User ID is incorrect', 312);

        } elseif(empty($this->premium_status)) {
            throw new AppException('Premium status is empty', 355);

        } elseif(!in_array($this->premium_status, ['hold', 'trash'])) {
            throw new AppException('Premium status is incorrect', 356);

        } elseif(empty($this->premium_code)) {
            throw new AppException('Premium code is empty', 357);

        } elseif(!is_string($this->premium_code) or mb_strlen($this->premium_code) < 2 or mb_strlen($this->premium_code) > 40) {
            throw new AppException('Premium code is incorrect', 358);

        } elseif(empty($this->premium_size)) {
            throw new AppException('Premium size is empty', 359);

        } elseif(!is_int($this->premium_size)) {
            throw new AppException('Premium size is incorrect', 360);

        } elseif(empty($this->premium_interval)) {
            throw new AppException('Premium interval is empty', 361);

        } elseif(!is_string($this->premium_interval) or mb_strlen($this->premium_interval) < 2 or mb_strlen($this->premium_interval) > 20) {
            throw new AppException('Premium interval is incorrect', 362);

        } elseif(!empty($this->referrer_key) and (!is_string($this->referrer_key) or mb_strlen($this->referrer_key) < 2 or mb_strlen($this->referrer_key) > 20)) {
            throw new AppException('Referrer key is incorrect', 363);
        }
    }
}
