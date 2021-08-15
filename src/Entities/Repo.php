<?php
namespace App\Entities;
use \App\Exceptions\AppException;

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

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\RepoTerm", mappedBy="repo", fetch="EXTRA_LAZY")
     * @JoinColumn(name="repo_id", referencedColumnName="id")
     */
    private $repo_terms;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\UserRole", mappedBy="repo", fetch="EXTRA_LAZY")
     * @JoinColumn(name="repo_id", referencedColumnName="id")
     */
    private $repo_roles;

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
    public function validate() {

        if(empty($this->create_date)) {
            throw new AppException('Create date is empty', 301);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('Create date is incorrect', 302);

        } elseif(empty($this->update_date)) {
            throw new AppException('Update date is empty', 303);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('Update date is incorrect', 304);

        } elseif(empty($this->user_id)) {
            throw new AppException('User ID is empty', 311);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('User ID is incorrect', 312);

        } elseif(empty($this->repo_name)) {
            throw new AppException('Repository name is empty', 324);

        } elseif(!is_string($this->repo_name) or mb_strlen($this->repo_name) < 2 or mb_strlen($this->repo_name) > 128) {
            throw new AppException('Repository name is incorrect', 325);
        }
    }
}
