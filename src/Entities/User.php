<?php
namespace App\Entities;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="users")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class User
{
    protected $error;

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

    /**
     * One user has many roles.
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Role", mappedBy="user", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user_roles;

    public function __construct() {
        $this->user_meta = new \Doctrine\Common\Collections\ArrayCollection();
        $this->error = '';
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

    protected function create_token() {
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

    /** @PrePersist */
    public function pre_persist() {
        $this->create_date = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $this->update_date = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone('Europe/Moscow'));
        $this->remind_date = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone('Europe/Moscow'));
        $this->user_status = 'pending';
        $this->user_token = $this->create_token();
        $this->user_pass = $this->create_pass();
        $this->user_hash = sha1($this->user_pass);

        if(empty($this->user_email)) {
            $this->error = 'User email is empty.';

        } elseif(mb_strlen($this->user_email) > 255) {
            $this->error = 'User email is too long.';

        } elseif(!preg_match("/^[a-z0-9._-]{2,123}@[a-z0-9._-]{2,123}\.[a-z]{2,8}$/", $this->user_email)) {
            $this->error = 'User email is incorrect.';

        } elseif(empty($this->user_name)) {
            $this->error = 'User name is empty.';

        } elseif(mb_strlen($this->user_name) < 4) {
            $this->error = 'User name is too short.';

        } elseif(mb_strlen($this->user_name) > 128) {
            $this->error = 'User name is too long.';
        }
    }


}