<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_roles")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Role
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
    private $hub_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('admin', 'editor', 'reader)") 
     * @var string
     */
    private $role_status;

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

    /** 
     * @PrePersist
     * @PreUpdate
     */
    public function validate() {

        if(empty($this->user_id)) {
            throw new AppException('Role error: user_id is empty.');

        } elseif(!is_numeric($this->user_id)) {
            throw new AppException('Role error: user_id is not numeric.');

        } elseif(empty($this->hub_id)) {
            throw new AppException('Role error: hub_id is empty.');

        } elseif(!is_numeric($this->hub_id)) {
            throw new AppException('Role error: hub_id is not numeric.');

        } elseif(empty($this->role_status)) {
            throw new AppException('Role error: role_status is empty.');

        } elseif(!in_array($this->role_status, ['admin', 'editor', 'reader'])) {
            throw new AppException('Role error: role_status is incorrect.');
        }
    }
}
