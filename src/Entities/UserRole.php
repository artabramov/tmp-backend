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
            throw new AppException('create_date is empty', 1401);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1402);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1403);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1404);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 1405);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 1406);

        } elseif(empty($this->repo_id)) {
            throw new AppException('repo_id is empty', 1407);

        } elseif(!is_int($this->repo_id)) {
            throw new AppException('repo_id is incorrect', 1408);

        } elseif(empty($this->role_status)) {
            throw new AppException('role_status is empty', 1409);

        } elseif(!in_array($this->role_status, ['admin', 'editor', 'reader'])) {
            throw new AppException('role_status is incorrect', 1410);
        }
    }
}
