<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users_terms")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class UserTerm
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
            throw new AppException('create_date is empty', 1202);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 1203);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 1204);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 1205);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 1206);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 1207);

        } elseif(empty($this->term_key)) {
            throw new AppException('term_key is empty', 1208);

        } elseif(!is_string($this->term_key) or mb_strlen($this->term_key) < 2 or mb_strlen($this->term_key) > 20) {
            throw new AppException('term_key is incorrect', 1209);

        } elseif(empty($this->term_value)) {
            throw new AppException('term_value is empty', 1210);

        } elseif(!is_string($this->term_value) or mb_strlen($this->term_value) < 2 or mb_strlen($this->term_value) > 255) {
            throw new AppException('term_value is incorrect', 1211);
        }
    }
}
