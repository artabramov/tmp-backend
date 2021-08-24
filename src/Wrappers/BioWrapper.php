<?php
namespace App\Wrappers;
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

class BioWrapper
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

    public function update(string $user_token, string $user_bio = '') {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- User bio --
        if(!empty($user_bio) and (mb_strlen($user_bio) < 2 or mb_strlen($user_bio) > 128)) {
            throw new AppException('User bio is incorrect', 221);
        }

        $user_term = $this->em->getRepository('\App\Entities\UserTerm')->findOneBy(['user_id' => $user->id, 'term_key' => 'user_bio']);

        if(empty($user_bio) and !empty($user_term)) {
            $this->em->remove($user_term);
            $this->em->flush();

        } elseif(!empty($user_bio) and empty($user_term)) {
            $user_term = new UserTerm();
            $user_term->create_date = Flight::datetime();
            $user_term->update_date = new DateTime('1970-01-01 00:00:00');
            $user_term->user_id = $user->id;
            $user_term->term_key = 'user_bio';
            $user_term->term_value = $user_bio;
            $user_term->user = $user;
            $this->em->persist($user_term);
            $this->em->flush();

        } elseif(!empty($user_bio) and !empty($user_term)) {
            $user_term->update_date = Flight::datetime();
            $user_term->term_value = $user_bio;
            $this->em->persist($user_term);
            $this->em->flush();
        }
        

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
