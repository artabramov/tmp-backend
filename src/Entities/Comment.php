<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="comments")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Comment
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
    private $post_id;

    /** 
     * @Column(type="string") 
     * @var string
     */
    private $comment_content;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Upload", mappedBy="comment", fetch="EXTRA_LAZY")
     * @JoinColumn(name="comment_id", referencedColumnName="id")
     */
    private $comment_uploads;

    public function __construct() {
        $this->comment_uploads = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
            throw new AppException('create_date is empty', 9001);

        } elseif(!$this->create_date  instanceof \DateTime) {
            throw new AppException('create_date is incorrect', 9002);

        } elseif(empty($this->update_date)) {
            throw new AppException('update_date is empty', 9003);

        } elseif(!$this->update_date  instanceof \DateTime) {
            throw new AppException('update_date is incorrect', 9004);

        } elseif(empty($this->user_id)) {
            throw new AppException('user_id is empty', 9005);

        } elseif(!is_int($this->user_id)) {
            throw new AppException('user_id is incorrect', 9006);

        } elseif(empty($this->post_id)) {
            throw new AppException('post_id is empty', 9007);

        } elseif(!is_int($this->post_id)) {
            throw new AppException('post_id is incorrect', 9008);

        } elseif(empty($this->comment_content)) {
            throw new AppException('comment_content is empty', 9009);

        } elseif(!is_string($this->comment_content) or mb_strlen($this->comment_content) < 4 or mb_strlen($this->comment_content) > 65535) {
            throw new AppException('post_title is incorrect', 9010);
        }
    }
    
}
