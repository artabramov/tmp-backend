<?php
namespace App\Entities;
use \App\Exceptions\AppException;

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
     * @Cache("NONSTRICT_READ_WRITE")
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     * @Cache("NONSTRICT_READ_WRITE")
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
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Comment", inversedBy="uploads", fetch="EXTRA_LAZY")
     * @JoinColumn(name="comment_id", referencedColumnName="id")
     */
    private $comment;

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

    /** 
     * @PrePersist
     * @PreUpdate
     */
    public function validate() {

        if(empty($this->user_id)) {
            throw new AppException('Upload error: user_id is empty.');

        } elseif(!is_numeric($this->user_id)) {
            throw new AppException('Upload error: user_id is not numeric.');

        } elseif(empty($this->comment_id)) {
            throw new AppException('Upload error: comment_id is empty.');

        } elseif(!is_numeric($this->comment_id)) {
            throw new AppException('Upload error: comment_id is not numeric.');

        } elseif(empty($this->upload_name)) {
            throw new AppException('Upload error: upload_name is empty.');

        } elseif(mb_strlen($this->upload_name) > 255) {
            throw new AppException('Upload error: upload_name is too long.');

        } elseif(empty($this->upload_file)) {
            throw new AppException('Upload error: upload_file is empty.');

        } elseif(mb_strlen($this->upload_file) > 255) {
            throw new AppException('Upload error: upload_file is too long.');

        } elseif(empty($this->upload_mime)) {
            throw new AppException('Upload error: upload_mime is empty.');

        } elseif(mb_strlen($this->upload_mime) > 255) {
            throw new AppException('Upload error: upload_mime is too long.');

        } elseif(empty($this->upload_size)) {
            throw new AppException('Upload error: upload_size is empty.');

        } elseif(!is_numeric($this->upload_size)) {
            throw new AppException('Upload error: upload_size is not numeric.');
        }
    }
    
}
