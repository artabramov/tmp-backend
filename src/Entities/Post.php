<?php
namespace App\Entities;
use \App\Exceptions\AppException;

/**
 * @Entity 
 * @HasLifecycleCallbacks
 * @Table(name="posts")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Post
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
     * @Column(type="string", columnDefinition="ENUM('todo', 'doing', 'done')") 
     * @var string
     */
    private $post_status;

    /** 
     * @Column(type="string", length="255") 
     * @var string
     */
    private $post_title;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\PostTerm", mappedBy="post", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post_terms;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\PostTag", mappedBy="post", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post_tags;

    /**
     * @Cache("NONSTRICT_READ_WRITE")
     * @OneToMany(targetEntity="\App\Entities\Alert", mappedBy="post", fetch="EXTRA_LAZY")
     * @JoinColumn(name="post_id", referencedColumnName="id")
     */
    private $post_alerts;

    public function __construct() {
        $this->post_terms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->post_tags = new \Doctrine\Common\Collections\ArrayCollection();
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

        } elseif(empty($this->repo_id)) {
            throw new AppException('Repository ID is empty', 322);

        } elseif(!is_int($this->repo_id)) {
            throw new AppException('Repository ID is incorrect', 323);

        } elseif(empty($this->post_status)) {
            throw new AppException('Post status is empty', 330);

        } elseif(!in_array($this->post_status, ['todo', 'doing', 'done'])) {
            throw new AppException('Post status is incorrect', 331);

        } elseif(empty($this->post_title)) {
            throw new AppException('Post title is empty', 332);

        } elseif(!is_string($this->post_title) or mb_strlen($this->post_title) < 2 or mb_strlen($this->post_title) > 255) {
            throw new AppException('Post title is incorrect', 333);
        }
    }
}
