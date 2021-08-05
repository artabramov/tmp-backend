<?php
namespace App\Entities;
use \App\Exceptions\UserException;

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
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $auth_date;

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

    /** 
     * @Column(type="string", length="20", nullable="true")
     * @var string
     */
    private $user_phone;

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
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Usermeta", mappedBy="user", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user_meta;

    public function __construct() {
        $this->user_meta = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function create_token() {
        return sha1(date('U')) . bin2hex(random_bytes(20));
    }

    public function create_pass() {
        $pass_symbols = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
        $pass_len = 8;
        $symbols_length = mb_strlen($pass_symbols, 'utf-8') - 1;
        $user_pass = '';
        for($i = 0; $i < $pass_len; $i++) {
            $user_pass .= $pass_symbols[random_int(0, $symbols_length)];
        }
        return $user_pass;
    }

    /** 
     * @PrePersist
     * @PreUpdate
     */
    public function validate() {

        if(empty($this->create_date)) {
            throw new UserException('create_date is empty', 1);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new UserException('create_date is incorrect', 2);

        } elseif(empty($this->update_date)) {
            throw new UserException('update_date is empty', 3);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new UserException('update_date is incorrect', 4);

        } elseif(empty($this->remind_date)) {
            throw new UserException('remind_date is empty', 5);

        } elseif(!$this->remind_date  instanceof \DateTime) {
            throw new UserException('remind_date is incorrect', 6);

        } elseif(empty($this->auth_date)) {
            throw new UserException('auth_date is empty', 7);

        } elseif(!$this->auth_date  instanceof \DateTime) {
            throw new UserException('auth_date is incorrect', 8);

        } elseif(empty($this->user_status)) {
            throw new UserException('user_status is empty', 9);

        } elseif(!in_array($this->user_status, ['pending', 'approved', 'trash'])) {
            throw new UserException('user_status is incorrect', 10);

        } elseif(empty($this->user_token)) {
            throw new UserException('user_token is empty', 11);

        } elseif(!preg_match("/^[0-9a-f]{80}$/", $this->user_token)) {
            throw new UserException('user_token is incorrect', 12);

        } elseif(!empty($this->user_hash) and !preg_match("/^[0-9a-f]{40}$/", $this->user_hash)) {
            throw new UserException('user_hash is incorrect', 13);

        } elseif(empty($this->user_email)) {
            throw new UserException('user_email is empty', 14);

        } elseif(mb_strlen($this->user_email) > 255 or !preg_match("/^[a-z0-9._-]{2,123}@[a-z0-9._-]{2,123}\.[a-z]{2,8}$/", $this->user_email)) {
            throw new UserException('user_email is incorrect', 15);

        } elseif(!empty($this->user_phone) and !preg_match("/^[0-9]{11,20}$/", $this->user_phone)) {
            throw new UserException('user_phone is incorrect', 16);

        } elseif(empty($this->user_name)) {
            throw new UserException('user_name is empty', 17);

        } elseif(mb_strlen($this->user_name) < 4 or mb_strlen($this->user_name) > 128) {
            throw new UserException('user_name is too incorrect', 18);
        }
    }

    
}