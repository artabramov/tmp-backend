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
    const UPLOAD_FILESIZE = '10M'; // max size of one file
    const UPLOAD_MIMES = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff', 'image/webp', 'application/pdf']; // available mimes: http://www.iana.org/assignments/media-types/media-types.xhtml
    const UPLOAD_THUMB_DIR = 'thumbs/';
    const UPLOAD_THUMB_WIDTH = 240;
    const UPLOAD_THUMB_FORMAT = 'jpeg';
    const UPLOAD_THUMB_MIMES = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff', 'image/webp'];

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

    public function insert(string $user_token, int $comment_id, array $files) {

        if(empty($files)) {
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
                return empty($tmp) ? 0 : (int) $tmp->term_value;
            }, $user->user_terms);

        $uploads_sum = call_user_func( 
            function($terms) {
                $tmp = $terms->filter(function($el) {
                    return $el->term_key == 'uploads_sum';
                })->first();
                return empty($tmp) ? 0 : (int) $tmp->term_value;
            }, $user->user_terms);

        if($uploads_sum >= $volume_size) {
            throw new AppException('uploads limit exceeded', 0);
        }

        // -- Original file --
        $upload_dir = self::UPLOAD_DIR . date('Y-m-d');
        if(!file_exists($upload_dir)) {
            try {
                mkdir($upload_dir, 0777, true);

            } catch (\Exception $e) {
                throw new AppException('make directory error', 0);
            }
        }

        $file_key = array_key_first($files);
        $file = new \Upload\File($file_key, new \Upload\Storage\FileSystem($upload_dir));
        $file->addValidations([new \Upload\Validation\Mimetype(self::UPLOAD_MIMES), new \Upload\Validation\Size(self::UPLOAD_FILESIZE)]);
        $file->setName(uniqid());

        // -- Thumb file --
        if(in_array($file->getMimetype(), self::UPLOAD_THUMB_MIMES)) {

            $thumb = new \phpThumb();
            $thumb->setSourceFilename($files[$file_key]['tmp_name']);
            $thumb->setParameter('w', self::UPLOAD_THUMB_WIDTH);
            $thumb->setParameter('config_output_format', self::UPLOAD_THUMB_FORMAT);
            $thumb->setParameter('config_allow_src_above_docroot', true);

            $thumb_file = uniqid() . '.' . $thumb->config_output_format;
            $thumb_dir = self::UPLOAD_THUMB_DIR . date('Y-m-d');
            $thumb_path = $thumb_dir . '/' . $thumb_file;

            if(!file_exists($thumb_dir)) {
                try {
                    mkdir($thumb_dir, 0777, true);

                } catch (\Exception $e) {
                    throw new AppException('make thumb dir error', 0);
                }
            }

            if ($thumb->GenerateThumbnail()) { 
                if (!$thumb->RenderToFile(__DIR__ . '/../../public/' . $thumb_path)) {
                    $thumb_path = null;
                }
            } else {
                $thumb_path = null;
            }
        }

        // -- Upload --
        $upload = new Upload();
        $upload->create_date = Flight::datetime();
        $upload->update_date = new DateTime('1970-01-01 00:00:00');
        $upload->user_id = $user->id;
        $upload->comment_id = $comment->id;
        $upload->upload_name = $files[$file_key]['name'];
        $upload->upload_path = $upload_dir . '/' . $file->getNameWithExtension();
        $upload->upload_mime = $file->getMimetype();
        $upload->upload_size = $file->getSize();
        $upload->thumb_path = !empty($thumb_path) ? $thumb_path : null;
        $upload->comment = $comment;

        try {
            $file->upload();
            $this->em->persist($upload);
            $this->em->flush();

        } catch (\Exception $e) {
            throw new AppException('file upload error', 0);
        }

        // -- Clear cache: post terms --
        foreach($post->post_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\PostTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\PostTerm', $term->id);
            }
        }

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
        }

        // -- Clear cache: user terms --
        foreach($user->user_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'upload' => [
                'id' => $upload->id
            ]
        ]);
    }

    public function delete(string $user_token, int $upload_id) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Upload --
        $upload = $this->em->find('\App\Entities\Upload', $upload_id);

        if(empty($upload)) {
            throw new AppException('upload not found', 0);
        }

        // -- Comment --
        $comment = $this->em->find('App\Entities\Comment', $upload->comment_id);

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

        } elseif($user_role->role_status != 'admin' and !($upload->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('permission denied', 0);
        }

        // -- Original file --
        if(file_exists($upload->upload_path)) {
            try {
                unlink($upload->upload_path);

            } catch (\Exception $e) {
                throw new AppException('file delete error', 0);
            }
        }

        // -- Thumb file --
        if(!empty($upload->thumb_path) and file_exists($upload->thumb_path)) {
            try {
                unlink($upload->thumb_path);

            } catch (\Exception $e) {
                throw new AppException('thumb delete error', 0);
            }
        }

        $this->em->remove($upload);
        $this->em->flush();

        // -- Clear cache: post terms --
        foreach($post->post_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\PostTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\PostTerm', $term->id);
            }
        }

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
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

    public function update(string $user_token, int $upload_id, string $upload_name) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Upload --
        $upload = $this->em->find('\App\Entities\Upload', $upload_id);

        if(empty($upload)) {
            throw new AppException('upload not found', 0);
        }

        // -- Comment --
        $comment = $this->em->find('App\Entities\Comment', $upload->comment_id);

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

        } elseif($user_role->role_status != 'admin' and !($upload->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('permission denied', 0);
        }

        //$upload->update_date = Flight::datetime();
        $upload->upload_name = $upload_name;
        $this->em->persist($upload);
        $this->em->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

}