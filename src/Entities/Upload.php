<?php
namespace App\Entities;

/**
 * @entity(table=uploads alias=upload)
 */
class Upload extends \artabramov\Echidna\Entity
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
    protected $comment_id;

    /**
     * @column(nullable=false unique=false regex=/^.{2,255}$/)
     */
    protected $upload_name;

    /**
     * @column(nullable=false unique=false regex=/^.{2,255}$/)
     */
    protected $upload_mime;

    /**
     * @column(nullable=false unique=false regex=/^[0-9]{1,20}$/)
     */
    protected $upload_size;

    /**
     * @column(nullable=false unique=false regex=/^.{2,255}$/)
     */
    protected $upload_file;

}
