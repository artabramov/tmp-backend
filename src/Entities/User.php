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
     * @Column(type="string", columnDefinition="ENUM('pending', 'approved', 'premium', 'trash')") 
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
        $this->create_date = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $this->update_date = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone('Europe/Moscow'));
        $this->remind_date = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone('Europe/Moscow'));
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

    /** @PrePersist */
    public function doOtherStuffOnPrePersist() {
        if(empty($this->user_name)) {
            $this->error = 'error!';
        }
    }


}