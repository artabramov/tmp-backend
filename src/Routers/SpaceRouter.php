<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Services\Email,
    \App\Wrappers\UserWrapper,
    \App\Wrappers\UserTermWrapper,
    \App\Wrappers\RepoWrapper,
    \App\Wrappers\RepoTermWrapper,
    \App\Wrappers\RoleWrapper,
    \App\Wrappers\PostWrapper,
    \App\Wrappers\PostTermWrapper,
    \App\Wrappers\CommentWrapper,
    \App\Wrappers\UploadWrapper,
    \App\Wrappers\BillingWrapper;

class SpaceRouter
{
    protected $em;
    protected $time;

    public function __construct($em, $time) {
        $this->em = $em;
        $this->time = $time;
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

    public function select(string $user_token, string $billing_code) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // billing
        $billing_wrapper = new BillingWrapper($this->em, $this->time);
        $billing = $billing_wrapper->select($billing_code);
        $billing = $billing_wrapper->approve($billing, $user->id);

        // user cache
        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($user->id, 'space_size');
        $user_term_wrapper->evict($user->id, 'space_expires');

        //$a = 1;

        /*
        $premium->user_id = $user->id;
        $premium->premium_status = 'trash';
        $this->em->persist($premium);
        $this->em->flush();

        // -- User volume --
        $user_volume = new UserVolume();
        $user_volume->create_date = Flight::datetime();
        $user_volume->update_date = new DateTime('1970-01-01 00:00:00');
        $user_volume->expires_date = Flight::datetime()->add(new DateInterval($premium->premium_interval));
        $user_volume->user_id = $user->id;
        $user_volume->volume_size = $premium->premium_size;
        $this->em->persist($user_volume);
        $this->em->flush();

        // -- Clear cache: user terms --
        foreach($user->user_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
            }
        }
        */

        return [ 
            'success' => 'true'
        ];
    }



}
