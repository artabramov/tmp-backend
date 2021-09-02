<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_roles")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class UserRole
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
     * @Column(type="integer")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @var int
     */
    private $repo_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('admin', 'editor', 'reader)") 
     * @var string
     */
    private $role_status;

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
            Halt::throw(1404); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1405); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1406); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1407); // update_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(1408); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(1409); // user_id is incorrect

        } elseif(empty($this->repo_id)) {
            Halt::throw(1410); // repo_id is empty

        } elseif(!is_int($this->repo_id)) {
            Halt::throw(1411); // repo_id is incorrect

        } elseif(empty($this->role_status)) {
            Halt::throw(1412); // role_status is empty

        } elseif(!in_array($this->role_status, ['admin', 'editor', 'reader'])) {
            Halt::throw(1413); // role_status is incorrect
        }
    }
}
