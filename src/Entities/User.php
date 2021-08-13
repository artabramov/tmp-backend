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
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $reset_date;

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
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\UserTerm", mappedBy="user", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user_terms;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\UserRole", mappedBy="user", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user_roles;

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
            throw new AppException('create_date is empty', 1001);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1002);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1003);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1004);

        } elseif(empty($this->remind_date)) {
            throw new AppException('remind_date is empty', 1005);

        } elseif(!$this->remind_date  instanceof \DateTime) {
            throw new AppException('remind_date is incorrect', 1006);

        } elseif(empty($this->reset_date)) {
            throw new AppException('reset_date is empty', 1007);

        } elseif(!$this->reset_date  instanceof \DateTime) {
            throw new AppException('reset_date is incorrect', 1008);

        } elseif(empty($this->user_status)) {
            throw new AppException('user_status is empty', 1011);

        } elseif(!in_array($this->user_status, ['pending', 'approved', 'trash'])) {
            throw new AppException('user_status is incorrect', 1012);

        } elseif(empty($this->user_token)) {
            throw new AppException('user_token is empty', 1013);

        } elseif(!is_string($this->user_token) or !preg_match("/^[0-9a-f]{80}$/", $this->user_token)) {
            throw new AppException('user_token is incorrect', 1014);

        } elseif(!empty($this->user_hash) and !preg_match("/^[0-9a-f]{40}$/", $this->user_hash)) {
            throw new AppException('user_hash is incorrect', 1015);

        } elseif(empty($this->user_email)) {
            throw new AppException('user_email is empty', 1016);

        } elseif(!is_string($this->user_email) or mb_strlen($this->user_email) > 255 or !preg_match("/^[a-z0-9._-]{2,123}@[a-z0-9._-]{2,123}\.[a-z]{2,8}$/", $this->user_email)) {
            throw new AppException('user_email is incorrect', 1017);

        } elseif(empty($this->user_name)) {
            throw new AppException('user_name is empty', 1018);

        } elseif(!is_string($this->user_name) or mb_strlen($this->user_name) < 2 or mb_strlen($this->user_name) > 128) {
            throw new AppException('user_name is incorrect', 1019);
        }
    }
    
}
