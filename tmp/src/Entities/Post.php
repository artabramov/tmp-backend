<?php
namespace App\Entities;

/**
 * @entity(table=posts entity=post)
 */
class Post extends \artabramov\Echidna\Entity
{
    /**
     * @column(nullable=true unique=true regex=/^[0-9]{1,20}$/)
     */
    protected $id;

    /**
     * @column(nullable=true unique=false regex=/^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}$/)
     */
    protected $create_date;

    /**
     * @column(nullable=true unique=false regex=/^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}$/)
     */
    protected $update_date;

    /**
     * @column(nullable=false unique=false regex=/^[0-9]{1,20}$/)
     */
    protected $user_id;

    /**
     * @column(nullable=false unique=false regex=/^[0-9]{1,20}$/)
     */
    protected $hub_id;

    /**
     * @column(nullable=false unique=false regex=/^todo$|^doing$|^done$/)
     */
    protected $post_status;

    /**
     * @column(nullable=false unique=false regex=/^.{2,255}$/)
     */
    protected $post_excerpt;

    /**
     * @column(nullable=true unique=false regex=/^[0-9]{1,20}$/)
     */
    protected $comments_count;

}
