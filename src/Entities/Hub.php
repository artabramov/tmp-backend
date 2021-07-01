<?php
namespace App\Entities;

/**
 * @Entity
 * @Table(name="hubs")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Hub
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
     * @Column(type="string", columnDefinition="ENUM('custom', 'trash')") 
     * @var string
     */
    private $hub_status;

    /** 
     * @Column(type="string", length="128")
     * @var string
     */
    private $hub_name;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Role", mappedBy="hub", fetch="EXTRA_LAZY")
     * @JoinColumn(name="hub_id", referencedColumnName="id")
     */
    private $users_roles;

    public function __construct() {
        $this->create_date = new \DateTime('now');
        $this->update_date = new \DateTime('1970-01-01 00:00:00');
        $this->user_roles = new \Doctrine\Common\Collections\ArrayCollection();
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

        if(empty($this->hub_status)) {
            $this->error = 'Hub error: hub_status is empty.';

        } elseif(!in_array($this->hub_status, ['custom', 'trash'])) {
            $this->error = 'Hub error: hub_status is incorrect.';

        } elseif(empty($this->user_id)) {
            $this->error = 'Hub error: user_id is empty.';

        } elseif(!is_numeric($this->user_id)) {
            $this->error = 'Hub error: user_id is not numeric.';

        } elseif(empty($this->hub_name)) {
            $this->error = 'Hub error: hub_name is empty.';

        } elseif(mb_strlen($this->hub_name) < 4) {
            $this->error = 'Hub error: hub_name is too short.';

        } elseif(mb_strlen($this->hub_name) > 128) {
            $this->error = 'Hub error: hub_name is too long.';
        }
    }
}
