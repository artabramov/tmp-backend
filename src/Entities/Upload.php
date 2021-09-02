<?php
namespace App\Entities;
use \App\Services\Halt;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="uploads")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Upload
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
    private $comment_id;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $upload_name;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $upload_file;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $upload_mime;

    /** 
     * @Column(type="integer")
     * @var string
     */
    private $upload_size;

    /** 
     * @Column(type="string", length="255")
     * @var string
     */
    private $thumb_file;

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
            Halt::throw(2104); // create_date is empty

        } elseif(!$this->create_date  instanceof \DateTime) {
            Halt::throw(2105); // create_date is incorrect

        } elseif(empty($this->update_date)) {
            Halt::throw(2106); // update_date is empty

        } elseif(!$this->update_date  instanceof \DateTime) {
            Halt::throw(2107); // update_date is incorrect

        } elseif(empty($this->user_id)) {
            Halt::throw(2108); // user_id is empty

        } elseif(!is_int($this->user_id)) {
            Halt::throw(2109); // user_id is incorrect

        } elseif(empty($this->comment_id)) {
            Halt::throw(2110); // comment_id is empty

        } elseif(!is_int($this->comment_id)) {
            Halt::throw(2111); // comment_id is incorrect

        } elseif(empty($this->upload_name)) {
            Halt::throw(2112); // upload_name is empty

        } elseif(!is_string($this->upload_name) or mb_strlen($this->upload_name) > 255) {
            Halt::throw(2113); // upload_name is incorrect

        } elseif(empty($this->upload_file)) {
            Halt::throw(2114); // upload_file is empty

        } elseif(!is_string($this->upload_file) or mb_strlen($this->upload_file) > 255) {
            Halt::throw(2115); // upload_file is incorrect

        } elseif(empty($this->upload_mime)) {
            Halt::throw(2117); // upload_mime is empty

        } elseif(!is_string($this->upload_mime) or mb_strlen($this->upload_mime) > 255) {
            Halt::throw(2118); // upload_mime is incorrect

        } elseif(empty($this->upload_size)) {
            Halt::throw(2119); // upload_size is empty

        } elseif(!is_int($this->upload_size)) {
            Halt::throw(2120); // upload_size is incorrect

        } elseif(!empty($this->thumb_file) and (!is_string($this->thumb_file) or mb_strlen($this->thumb_file) > 255)) {
            Halt::throw(2121); // thumb_file is incorrect
        }
    }
}
