<?php
namespace App\Entities;
use \App\Exceptions\AppException;

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

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\User", inversedBy="user_roles", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Repo", inversedBy="user_roles", fetch="EXTRA_LAZY")
     * @JoinColumn(name="repo_id", referencedColumnName="id")
     */
    private $repo;

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

        } elseif(empty($this->repo_id)) {
            throw new AppException('Repository ID is empty', 322);

        } elseif(!is_int($this->repo_id)) {
            throw new AppException('Repository ID is incorrect', 323);

        } elseif(empty($this->role_status)) {
            throw new AppException('Role status is empty', 326);

        } elseif(!in_array($this->role_status, ['admin', 'editor', 'reader'])) {
            throw new AppException('Role status is incorrect', 327);
        }
    }
}
