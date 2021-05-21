<?php
namespace App\Entities;

/**
 * @entity(table=users alias=user)
 */
class User extends \artabramov\Echidna\Entity
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
     * @column(nullable=true unique=false regex=/^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}$/)
     */
    protected $restore_date;

    /**
     * @column(nullable=false unique=false regex=/^pending$|^approved$|^trash$/)
     */
    protected $user_status;

    /**
     * @column(nullable=false unique=true regex=/^[0-9a-f]{80}$/)
     */
    protected $user_token;

    /**
     * @column(nullable=false unique=true regex=/^[a-z0-9._-]{2,123}@[a-z0-9._-]{2,123}\.[a-z]{2,8}$/)
     */
    protected $user_email;

    /**
     * @column(nullable=false unique=false regex=/^.{2,255}$/)
     */
    protected $user_name;

    /**
     * @column(nullable=true unique=false regex=/^[0-9a-f]{40}$/)
     */
    protected $user_hash;

    protected $user_pass;

    public function pass() {

        $pass_symbols = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $pass_len = 10;
    
        $symbols_length = mb_strlen( $pass_symbols, 'utf-8' ) - 1;
        $user_pass = '';
    
        for( $i = 0; $i < $pass_len; $i++ ) {
            $user_pass .= $pass_symbols[ random_int( 0, $symbols_length ) ];
        }
        return $user_pass;
    }

    public function hash( $value ) {
        return sha1( $value . '~salt' );
    }

    public function token() {
        return sha1( date( 'U' )) . bin2hex( random_bytes( 20 ));
    }
}
