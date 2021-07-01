<?php
namespace App\Entities;

/**
 * @Entity
 * @Table(name="users_roles")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Role
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
     * @Column(type="integer")
     * @Cache("NONSTRICT_READ_WRITE")
     * @var int
     */
    private $hub_id;

    /** 
     * @Column(type="string", columnDefinition="ENUM('custom', 'trash')") 
     * @var string
     */
    private $role_status;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Hub", inversedBy="users_roles", fetch="EXTRA_LAZY")
     * @JoinColumn(name="hub_id", referencedColumnName="id")
     */
    private $hub;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\User", inversedBy="users_roles", fetch="EXTRA_LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function __construct() {
        $this->create_date = new \DateTime('now');
        $this->update_date = new \DateTime('1970-01-01 00:00:00');
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

    public function validate() {

        if(empty($this->user_id)) {
            $this->error = 'Role error: user_id is empty.';

        } elseif(!is_numeric($this->user_id)) {
            $this->error = 'Role error: user_id is not numeric.';

        } elseif(empty($this->hub_id)) {
            $this->error = 'Role error: hub_id is empty.';

        } elseif(!is_numeric($this->hub_id)) {
            $this->error = 'Role error: hub_id is not numeric.';

        } elseif(empty($this->role_status)) {
            $this->error = 'Role error: role_status is empty.';

        } elseif(!in_array($this->role_status, ['admin', 'writer', 'reader'])) {
            $this->error = 'Role error: role_status is incorrect.';
        }
    }
}
