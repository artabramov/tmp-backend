<?php
namespace App\Entities;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_meta")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Usermeta
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
     * @Column(type="integer")
     * @Cache("NONSTRICT_READ_WRITE")
     * @var int
     */
    private $user_id;

    /** 
     * @Column(type="string", length="20") 
     * @var string
     */
    private $meta_key;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $meta_value;

    /**
     * Many metas have one user. This is the owning side.
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\User", inversedBy="user_meta", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function __construct() {
        $this->create_date = new \DateTime('now', new \DateTimeZone(APP_TIMEZONE));
        $this->update_date = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone(APP_TIMEZONE));
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
    public function prePersist() {
        $this->create_date = new \DateTime('now', new \DateTimeZone(APP_TIMEZONE));
        $this->update_date = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone(APP_TIMEZONE));

        if(empty($this->user_id)) {
            $this->error = 'Meta error: user id is empty.';

        } elseif(!is_numeric($this->user_id)) {
            $this->error = 'Meta error: user id is not numeric.';

        } elseif(empty($this->meta_key)) {
            $this->error = 'Meta error: key is empty.';

        } elseif(mb_strlen($this->meta_key) > 20) {
            $this->error = 'Meta error: key is too long.';

        } elseif(empty($this->meta_value)) {
            $this->error = 'Meta error: value is empty.';

        } elseif(mb_strlen($this->meta_value) > 255) {
            $this->error = 'Meta error: value is too long.';
        }
    }
}
