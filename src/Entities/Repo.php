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
            throw new AppException('create_date is empty', 1201);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1202);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1203);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1204);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 1205);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 1206);

        } elseif(empty($this->repo_name)) {
            throw new AppException('repo_name is empty', 1207);

        } elseif(!is_string($this->repo_name) or mb_strlen($this->repo_name) < 2 or mb_strlen($this->repo_name) > 128) {
            throw new AppException('repo_name is incorrect', 1208);
        }
    }
}
