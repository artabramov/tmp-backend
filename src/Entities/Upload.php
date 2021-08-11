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
            throw new AppException('create_date is empty', 2001);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 2002);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 2003);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 2004);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 2005);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 2006);

        } elseif(empty($this->comment_id)) {
            throw new AppException('comment_id is empty', 2007);

        } elseif(!is_int($this->comment_id)) {
            throw new AppException('comment_id is incorrect', 2008);

        } elseif(empty($this->upload_name)) {
            throw new AppException('upload_name is empty', 2009);

        } elseif(!is_string($this->upload_name) or mb_strlen($this->upload_name) > 255) {
            throw new AppException('upload_name is incorrect', 2010);

        } elseif(empty($this->upload_path)) {
            throw new AppException('upload_path is empty', 2011);

        } elseif(!is_string($this->upload_path) or mb_strlen($this->upload_path) > 255) {
            throw new AppException('upload_path is incorrect', 2012);

        } elseif(empty($this->upload_mime)) {
            throw new AppException('upload_mime is empty', 2013);

        } elseif(!is_string($this->upload_mime) or mb_strlen($this->upload_mime) > 255) {
            throw new AppException('upload_mime is incorrect', 2014);

        } elseif(empty($this->upload_size)) {
            throw new AppException('upload_size is empty', 2015);

        } elseif(!is_int($this->upload_size)) {
            throw new AppException('upload_size is incorrect', 2016);

        } elseif(!empty($this->thumb_path) and (!is_string($this->thumb_path) or mb_strlen($this->thumb_path) > 255)) {
            throw new AppException('thumb_path is incorrect', 2018);
        }
    }
}
