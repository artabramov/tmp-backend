<?php
namespace App\Routers;
use \Flight,
    \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \App\Exceptions\AppException,
    \App\Entities\User,
    \App\Entities\UserTerm,
    \App\Entities\Repo,
    \App\Entities\RepoTerm,
    \App\Entities\UserRole,
    \App\Entities\Post,
    \App\Entities\PostTerm,
    \App\Entities\PostTag,
    \App\Entities\PostAlert,
    \App\Entities\Comment,
    \App\Entities\Upload,
    \App\Entities\UserVolume,
    \App\Entities\Premium;

class PremiumRouter
{
    protected $em;

    public function __construct($em) {
        $this->em = $em;
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

    public function select(string $user_token, string $premium_code) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Premium --
        $premium = $this->em->getRepository('\App\Entities\Premium')->findOneBy(['premium_code' => $premium_code]);

        if(empty($premium)) {
            throw new AppException('Premium not found', 217);

        } elseif($premium->premium_status == 'trash') {
            throw new AppException('Premium deleted', 218);
        }

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

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }



}
