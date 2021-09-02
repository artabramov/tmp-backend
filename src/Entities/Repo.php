<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="repos")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Repo
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
     * @Column(type="string", length="128")
     * @var string
     */
    private $repo_name;

    public function __construct() {
        $this->repo_terms = new \Doctrine\Common\Collections\ArrayCollection();
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
            Halt::throw(1504); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1505); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1506); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1507); // update_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(1508); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(1509); // user_id is incorrect

        } elseif(empty($this->repo_name)) {
            Halt::throw(1510); // repo_name is empty

        } elseif(!is_string($this->repo_name) or mb_strlen($this->repo_name) < 2 or mb_strlen($this->repo_name) > 128) {
            Halt::throw(1511); // repo_name is incorrect
        }
    }
}
