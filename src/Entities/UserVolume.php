<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_volumes")
 */
class UserVolume
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
     * @Column(type="integer")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $volume_size;

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
            Halt::throw(1306); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1307); // update_date is incorrect

        } elseif(empty($this->expires_date)) {
            Halt::throw(1308); // expires_date is empty

        } elseif(!$this->expires_date  instanceof \DateTime) {
            Halt::throw(1309); // expires_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(1310); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(1311); // user_id is incorrect

        } elseif(empty($this->volume_size)) {
            Halt::throw(1312); // volume_size is empty

        } elseif(!is_int($this->volume_size)) {
            Halt::throw(1313); // volume_size is incorrect
        }
    }
}
