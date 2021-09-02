<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="users")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class User
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
    protected $remind_date;

    /** 
     * @Column(type="string", columnDefinition="ENUM('pending', 'approved', 'trash')") 
     * @var string
     */
    private $user_status;

    /** 
     * @Column(type="string", length="80", unique="true") 
     * @var string
     */
    private $user_token;

    /** 
     * @Column(type="string", length="255", unique="true")
     * @var string
     */
    private $user_email;

    private $user_pass;

    /** 
     * @Column(type="string", length="40", nullable="true")
     * @var string
     */
    private $user_hash;

    /** 
     * @Column(type="string", length="128")
     * @var string
     */
    private $user_name;

    /** 
     * @Column(type="string", length="80")
     * @var string
     */
    private $user_timezone;

    public function __construct() {
        $this->user_terms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->user_roles = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
            Halt::throw(1104); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1105); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1106); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1107); // update_date is incorrect

        } elseif(empty($this->remind_date)) {
            Halt::throw(1108); // remind_date is empty

        } elseif(!$this->remind_date  instanceof \DateTime) {
            Halt::throw(1109); // remind_date is incorrect

        } elseif(empty($this->user_status)) {
            Halt::throw(1110); // user_status is empty

        } elseif(!in_array($this->user_status, ['pending', 'approved', 'trash'])) {
            Halt::throw(1111); // user_status is incorrect

        } elseif(empty($this->user_token)) {
            Halt::throw(1113); // user_token is empty

        } elseif(!is_string($this->user_token) or !preg_match("/^[0-9a-f]{80}$/", $this->user_token)) {
            Halt::throw(1114); // user_token is incorrect

        } elseif(!empty($this->user_hash) and !preg_match("/^[0-9a-f]{40}$/", $this->user_hash)) {
            Halt::throw(1116); // user_hash is incorrect

        } elseif(empty($this->user_email)) {
            Halt::throw(1118); // user_email is empty
            
        } elseif(!is_string($this->user_email) or mb_strlen($this->user_email) > 255 or !preg_match("/^[a-z0-9._-]{2,123}@[a-z0-9._-]{2,123}\.[a-z]{2,8}$/", $this->user_email)) {
            Halt::throw(1119); // user_email is incorrect

        } elseif(empty($this->user_name)) {
            Halt::throw(1121); // user_name is empty

        } elseif(!is_string($this->user_name) or mb_strlen($this->user_name) < 2 or mb_strlen($this->user_name) > 128) {
            Halt::throw(1122); // user_name is incorrect

        } elseif(empty($this->user_timezone)) {
            Halt::throw(1123); // user_timezone is empty

        } elseif(!in_array($this->user_timezone, \DateTimeZone::listIdentifiers())) {
            Halt::throw(1124); // user_timezone is incorrect
        }
    }
}
