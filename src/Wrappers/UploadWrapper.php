<?php
namespace App\Wrappers;
use \Flight,
    \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \App\Exceptions\AppException,
    \App\Entities\User,       // 10..
    \App\Entities\UserTerm,   // 11..
    \App\Entities\Repo,       // 12..
    \App\Entities\RepoTerm,   // 13..
    \App\Entities\UserRole,   // 14..
    \App\Entities\Post,       // 15..
    \App\Entities\PostTerm,   // 16..
    \App\Entities\PostTag,    // 17..
    \App\Entities\PostAlert,  // 18..
    \App\Entities\Comment,    // 19..
    \App\Entities\Upload,     // 20..
    \App\Entities\UserVolume, // 21..
    \App\Entities\Premium;    // 22..

class UploadWrapper
{
    protected $em;

    const UPLOAD_INSERT_LIMIT = 128; // maximum uploads number per one comment
    const UPLOAD_DIR = 'uploads/';

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

    public function insert(string $user_token, int $comment_id, array $file) {

        if(empty($file)) {
            throw new AppException('file are empty', 0);
        }

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Comment --
        $comment = $this->em->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('comment not found', 0);
        }

        // -- Post --
        $post = $this->em->find('App\Entities\Post', $comment->post_id);

        if(empty($post)) {
            throw new AppException('post not found', 0);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('repo not found', 0);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('role not found', 0);

        } elseif($user_role->role_status != 'admin' and !($comment->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('permission denied', 0);
        }

        // -- Filter: user volume --
        $volume_size = call_user_func( 
            function($terms) {
                $tmp = $terms->filter(function($el) {
                    return $el->term_key == 'volume_size';
                })->first();
                return empty($tmp) ? 0 : $tmp->term_value;
            }, $user->user_terms);

        /*
        // -- User vol --
        $qb1 = $em->createQueryBuilder();
        $qb1->select('vol.id')
            ->from('App\Entities\Vol', 'vol')
            ->where($qb1->expr()->eq('vol.user_id', $user->id))
            ->andWhere($qb1->expr()->gt('vol.expire_date', ':now'))
            ->setParameter('now', 'NOW()')
            ->orderBy('vol.vol_size', 'DESC')
            ->setMaxResults(1);

        $qb1_result = $qb1->getQuery()->getResult();
        $user_vol = $em->find('App\Entities\Vol', $qb1_result[0]['id']);
        */

        // -- Make dir --
        $dir = self::UPLOAD_DIR . date('Y-m-d');
        if(!file_exists($dir)) {
            try {
                mkdir($dir, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('make directory error', 0);
            }
        }

        /*
        $uploads = [];
        foreach($files->keys() as $key) {

            // -- File --
            $file = new \Upload\File($key, new \Upload\Storage\FileSystem($path));
            $file->addValidations([new \Upload\Validation\Mimetype(UPLOAD_MIMES), new \Upload\Validation\Size(UPLOAD_FILESIZE)]);
            $file->setName(uniqid());

            // -- Uploads sum --
            $user_meta = $em->getRepository('\App\Entities\Usermeta')->findOneBy(['user_id' => $user->id, 'meta_key' => 'uploads_sum']);
            if(!empty($user_meta) and (int) $user_meta->meta_value + (int) $file->getSize() >= $user_vol->vol_size) {
                throw new AppException('Upload error: vol size limit exceeded.');
            }

            // -- Upload --
            $upload = new Upload();
            $upload->create_date = Flight::get('date');
            $upload->update_date = Flight::get('zero');
            $upload->user_id = $user->id;
            $upload->comment_id = $comment->id;
            $upload->upload_name = $files[$key]['name'];
            $upload->upload_file = $path . '/' . $file->getNameWithExtension();
            $upload->upload_mime = $file->getMimetype();
            $upload->upload_size = $file->getSize();
            $upload->post_comment = $comment;

            try {
                $file->upload();
                $em->persist($upload);
                $em->flush();
                array_push($uploads, $upload);

            } catch (\Exception $e) {
                throw new AppException('Upload error: file upload error.');
            }

            // -- Usermeta cache --
            foreach($user->user_meta->getValues() as $meta) {
                if($em->getCache()->containsEntity('\App\Entities\Usermeta', $meta->id)) {
                    $em->getCache()->evictEntity('\App\Entities\Usermeta', $meta->id);
                }
            }
        }
        */

        // -- End --
        Flight::json([ 
            'success' => 'true',
            //'upload_id' => array_map(fn($n) => $n->id, $uploads)
        ]);
    }

}
