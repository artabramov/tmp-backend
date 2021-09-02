<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="repos_terms")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class RepoTerm
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
    private $repo_id;

    /** 
     * @Column(type="string", length="20") 
     * @var string
     */
    private $term_key;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $term_value;

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
            Halt::throw(1604); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(1605); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(1606); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(1607); // update_date is incorrect

        } elseif(empty($this->repo_id)) {
            Halt::throw(1608); // repo_id is empty

        } elseif(!is_int($this->repo_id)) {
            Halt::throw(1609); // repo_id is incorrect

        } elseif(empty($this->term_key)) {
            Halt::throw(1610); // term_key is empty

        } elseif(!is_string($this->term_key) or mb_strlen($this->term_key) < 2 or mb_strlen($this->term_key) > 20) {
            Halt::throw(1611); // term_key is incorrect

        } elseif(empty($this->term_value)) {
            Halt::throw(1612); // term_value is empty

        } elseif(!is_string($this->term_value) or mb_strlen($this->term_value) < 2 or mb_strlen($this->term_value) > 255) {
            Halt::throw(1613); // term_value is incorrect
        }
    }
}
