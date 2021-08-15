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
    private $upload_path;

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
    private $thumb_path;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @ManyToOne(targetEntity="\App\Entities\Comment", inversedBy="comment_uploads", fetch="EXTRA_LAZY")
     * @JoinColumn(name="comment_id", referencedColumnName="id")
     */
    private $comment;

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

        } elseif(empty($this->comment_id)) {
            throw new AppException('Comment ID is empty', 334);

        } elseif(!is_int($this->comment_id)) {
            throw new AppException('Comment ID is incorrect', 335);

        } elseif(empty($this->upload_name)) {
            throw new AppException('Upload name is empty', 338);

        } elseif(!is_string($this->upload_name) or mb_strlen($this->upload_name) > 255) {
            throw new AppException('Upload name is incorrect', 339);

        } elseif(empty($this->upload_path)) {
            throw new AppException('Upload path is empty', 340);

        } elseif(!is_string($this->upload_path) or mb_strlen($this->upload_path) > 255) {
            throw new AppException('Upload path is incorrect', 341);

        } elseif(empty($this->upload_mime)) {
            throw new AppException('Upload MIME type is empty', 342);

        } elseif(!is_string($this->upload_mime) or mb_strlen($this->upload_mime) > 255) {
            throw new AppException('Upload MIME type is incorrect', 343);

        } elseif(empty($this->upload_size)) {
            throw new AppException('Upload size is empty', 344);

        } elseif(!is_int($this->upload_size)) {
            throw new AppException('Upload size is incorrect', 345);

        } elseif(!empty($this->thumb_path) and (!is_string($this->thumb_path) or mb_strlen($this->thumb_path) > 255)) {
            throw new AppException('Thumb path is incorrect', 346);
        }
    }
}
