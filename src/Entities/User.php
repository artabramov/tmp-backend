<?php
namespace App\Entities;

/**
 * @entity(name=user table=users)
 */
class User extends \App\Core\Entity
{
    /**
     * @column(name=id type=integer unique=true nullable=true)
     */
    protected $id;

    /**
     * @column(name=user_email type=string length=255 nullable=false unique=true regex=/^\S+@\S+\.\S+$/)
     */
    protected $user_email;

    /**
     * @column(name=user_name type=string length=10)
     */
    protected $user_name;

    /**
     * @column(name=user_token nullable=false type=string regex=/^[0-9a-f]{80}$/)
     */
    protected $user_token;

    /**
     * @column(name=user_hash nullable=true type=string regex=/^[0-9a-f]{40}$/)
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
