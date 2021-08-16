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

class UserpicWrapper
{
    protected $em;

    const USERPIC_DIR = 'thumbs/';
    const USERPIC_MIMES = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff'];
    const USERPIC_FILESIZE = 10000000; // max size in bytes
    const USERPIC_WIDTH = 160;
    const USERPIC_HEIGHT = 160;
    const USERPIC_FORMAT = 'jpeg';

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

    public function insert(string $user_token, array $files) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Original file --
        $tmp = $files[array_key_first($files)];
        if(!in_array($tmp['type'], self::USERPIC_MIMES)) {
            throw new AppException('File type not allowed', 108);

        } elseif($tmp['size'] > self::USERPIC_FILESIZE) {
            throw new AppException('File is too large', 109);
        }

        // -- User thumb --
        $thumb = new \phpThumb();
        $thumb->setSourceFilename($tmp['tmp_name']);
        $thumb->setParameter('w', self::USERPIC_WIDTH);
        $thumb->setParameter('h', self::USERPIC_HEIGHT);
        $thumb->setParameter('zc', 1);
        $thumb->setParameter('config_output_format', self::USERPIC_FORMAT);
        $thumb->setParameter('config_allow_src_above_docroot', true);

        $thumb_file = uniqid() . '.' . $thumb->config_output_format;
        $thumb_dir = self::USERPIC_DIR . date('Y-m-d');
        $thumb_path = $thumb_dir . '/' . $thumb_file;

        if(!file_exists($thumb_dir)) {
            try {
                mkdir($thumb_dir, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('Directory make failed', 103);
            }
        }

        if ($thumb->GenerateThumbnail()) { 
            if (!$thumb->RenderToFile(__DIR__ . '/../../public/' . $thumb_path)) {
                throw new AppException('File write failed', 106);
            }
        } else {
            throw new AppException('File write failed', 106);
        }

        // -- 
        $user_term = $this->em->getRepository('\App\Entities\UserTerm')->findOneBy(['user_id' => $user->id, 'term_key' => 'thumb_path']);
        if(!empty($user_term)) {
            if(!empty($user_term->term_value) and file_exists($user_term->term_value)) {
                try {
                    unlink($user_term->term_value);
    
                } catch (\Exception $e) {
                    throw new AppException('File delete failed', 107);
                }
            }

            $user_term->update_date = Flight::datetime();
            $user_term->term_value = $thumb_path;
            $this->em->persist($user_term);
            $this->em->flush();

        } else {
            $user_term = new UserTerm();
            $user_term->create_date = Flight::datetime();
            $user_term->update_date = new DateTime('1970-01-01 00:00:00');
            $user_term->user_id = $user->id;
            $user_term->term_key = 'thumb_path';
            $user_term->term_value = $thumb_path;
            $user_term->user = $user;
            $this->em->persist($user_term);
            $this->em->flush();
        }

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

}
