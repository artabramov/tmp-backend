<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="billings")
 */
class Billing
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
     * @Column(type="integer", nullable="true")
     * @var int
     */
    private $user_id;

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
     * @Column(type="string", columnDefinition="ENUM('pending', 'approved')") 
     * @var string
     */
    private $billing_status;

    /**
     * @Column(type="string", length="40", unique="true")
     * @var int
     */
    private $billing_code;

    /**
     * @Column(type="float")
     * @var int
     */
    private $billing_sum;

    /** 
     * @Column(type="string", columnDefinition="ENUM('RUB', 'USD', 'EUR', 'GBP', 'CHF', 'CNY', 'JPY')") 
     * @var string
     */
    private $billing_currency;

    /**
     * @Column(type="string", length="255", nullable="true")
     * @var int
     */
    private $billing_comment;

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
            Halt::throw(1304); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1305); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1306); // update date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1307); // update_date is incorrect

        } elseif(empty($this->expires_date)) {
            Halt::throw(1308); // expires_date is empty

        } elseif(!$this->expires_date  instanceof \DateTime) {
            Halt::throw(1309); // expires_date is incorrect

        } elseif(!empty($this->user_id) and !is_int($this->user_id)) {
            Halt::throw(1310); // user_id is incorrect

        } elseif(empty($this->space_size)) {
            Halt::throw(1311); // space_size is empty

        } elseif(!is_int($this->space_size)) {
            Halt::throw(1312); // space_size is incorrect

        } elseif(empty($this->space_interval)) {
            Halt::throw(1313); // space_interval is empty

        } elseif(!is_string($this->space_interval) or mb_strlen($this->space_interval) < 2 or mb_strlen($this->space_interval) > 20) {
            Halt::throw(1314); // space_interval is incorrect

        } elseif(empty($this->billing_status)) {
            Halt::throw(1315); // billing_status is empty

        } elseif(!in_array($this->billing_status, ['pending', 'approved'])) {
            Halt::throw(1316); // billing_status is incorrect

        } elseif(empty($this->billing_code)) {
            Halt::throw(1317); // billing_code is empty

        } elseif(!is_string($this->billing_code) or mb_strlen($this->billing_code) < 2 or mb_strlen($this->billing_code) > 40) {
            Halt::throw(1318); // billing_code is incorrect

        } elseif(!empty($this->billing_sum) and !is_float($this->billing_sum)) {
            Halt::throw(1320); // billing_sum is incorrect

        } elseif(empty($this->billing_currency)) {
            Halt::throw(1311); // billing_currency is empty

        } elseif(!in_array($this->billing_currency, ['RUB', 'USD', 'EUR', 'GBP', 'CHF', 'CNY', 'JPY'])) {
            Halt::throw(1322); // billing_currency is incorrect

        } elseif(!empty($this->billing_comment) and (!is_string($this->billing_comment) or mb_strlen($this->referrer_key) < 2 or mb_strlen($this->billint_comment) > 255)) {
            Halt::throw(1323); // billing_comment is incorrect
        }
    }
}
