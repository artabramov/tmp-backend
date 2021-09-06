<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_spaces")
 */
class UserSpace
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
    protected $approve_date;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $expires_date;

    /**
     * @Column(type="integer", nullable="true")
     * @var int
     */
    private $user_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('pending', 'approved')") 
     * @var string
     */
    private $space_status;

    /**
     * @Column(type="string", length="20")
     * @var int
     */
    private $space_key;

    /**
     * @Column(type="string", length="40", unique="true")
     * @var int
     */
    private $space_code;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $space_size;

    /** 
     * @Column(type="string", length="20")
     * @var string
     */
    private $space_interval;

    /**
     * @Column(type="float")
     * @var int
     */
    private $space_cost;

    /** 
     * @Column(type="string", columnDefinition="ENUM('RUB', 'USD', 'EUR', 'GBP', 'CHF', 'CNY', 'JPY')") 
     * @var string
     */
    private $space_currency;

    /**
     * @Column(type="string", length="255", nullable="true")
     * @var int
     */
    private $space_comment;

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
    public function pre() {

        if(empty($this->create_date)) {
            Halt::throw(2304); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(2305); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(2306); // update date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(2307); // update_date is incorrect

        } elseif(empty($this->trash_date)) {
            Halt::throw(2308); // trash_date is empty

        } elseif(!$this->trash_date  instanceof \DateTime) {
            Halt::throw(2309); // trash_date is incorrect

        } elseif(!empty($this->user_id) and !is_int($this->user_id)) {
            Halt::throw(2311); // user_id is incorrect

        } elseif(empty($this->premium_status)) {
            Halt::throw(2312); // premium_status is empty

        } elseif(!in_array($this->premium_status, ['hold', 'trash'])) {
            Halt::throw(2313); // premium_status is incorrect

        } elseif(empty($this->premium_code)) {
            Halt::throw(2315); // premium_code is empty

        } elseif(!is_string($this->premium_code) or mb_strlen($this->premium_code) < 2 or mb_strlen($this->premium_code) > 40) {
            Halt::throw(2316); // premium_code is incorrect

        } elseif(empty($this->premium_size)) {
            Halt::throw(2317); // premium_size is empty

        } elseif(!is_int($this->premium_size)) {
            Halt::throw(2318); // premium_size is incorrect

        } elseif(empty($this->premium_interval)) {
            Halt::throw(2319); // premium_interval is empty

        } elseif(!is_string($this->premium_interval) or mb_strlen($this->premium_interval) < 2 or mb_strlen($this->premium_interval) > 20) {
            Halt::throw(2320); // premium_interval is incorrect

        } elseif(!empty($this->premium_sum) and !is_int($this->premium_sum)) {
            Halt::throw(2322); // premium_sum is incorrect

        } elseif(!empty($this->premium_currency) and !in_array($this->premium_currency, ['RUB', 'USD', 'EUR', 'GBP', 'CHF', 'CNY', 'JPY'])) {
            Halt::throw(2324); // premium_currency is incorrect

        } elseif(!empty($this->referrer_key) and (!is_string($this->referrer_key) or mb_strlen($this->referrer_key) < 2 or mb_strlen($this->referrer_key) > 20)) {
            Halt::throw(2326); // referrer_key is incorrect
        }
    }
}
