<?php
namespace App\Entities;

/**
 * @entity(table=post_meta alias=meta)
 */
class Postmeta extends \artabramov\Echidna\Entity
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
    protected $post_id;

    /**
     * @column(nullable=false unique=false regex=/^[a-z0-9_]{1,20}$/)
     */
    protected $meta_key;

    /**
     * @column(nullable=false unique=false regex=/^.{0,255}$/)
     */
    protected $meta_value;

}
