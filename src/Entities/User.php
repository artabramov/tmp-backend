<?php
namespace App\Entities;
use \App\Exceptions\AppException;

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
     * @Column(type="string", length="40")
     * @var string
     */
    private $user_hash;

    /** 
     * @Column(type="string", length="128")
     * @var string
     */
    private $user_name;

    /**
     * One user has many metas. This is the inverse side.
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Usermeta", mappedBy="user", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user_meta;

    public function __construct() {
        $this->create_date = new \DateTime('now');
        $this->update_date = new \DateTime('1970-01-01 00:00:00');
        $this->remind_date = new \DateTime('1970-01-01 00:00:00');
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

        if(empty($this->user_status)) {
            throw new AppException('User error: user_status is empty.');

        } elseif(!in_array($this->user_status, ['pending', 'approved', 'trash'])) {
            throw new AppException('User error: user_status is incorrect.');

        } elseif(empty($this->user_token)) {
            throw new AppException('User error: user_token is empty.');

        } elseif(!preg_match("/^[0-9a-f]{80}$/", $this->user_token)) {
            throw new AppException('User error: user_token is incorrect.');

        } elseif(!empty($this->user_hash) and !preg_match("/^[0-9a-f]{40}$/", $this->user_hash)) {
            throw new AppException('User error: user_hash is incorrect.');

        } elseif(empty($this->user_email)) {
            throw new AppException('User error: user_email is empty.');

        } elseif(mb_strlen($this->user_email) > 255) {
            throw new AppException('User error: user_email is too long.');

        } elseif(!preg_match("/^[a-z0-9._-]{2,123}@[a-z0-9._-]{2,123}\.[a-z]{2,8}$/", $this->user_email)) {
            throw new AppException('User error: user_email is incorrect.');

        } elseif(empty($this->user_name)) {
            throw new AppException('User error: user_name is empty.');

        } elseif(mb_strlen($this->user_name) < 4) {
            throw new AppException('User error: user_name is too short.');

        } elseif(mb_strlen($this->user_name) > 128) {
            throw new AppException('User error: user_name is too long.');
        }
    }

    
}