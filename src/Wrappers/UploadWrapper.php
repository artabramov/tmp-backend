<?php
namespace App\Wrappers;
use \DateTime,
    \DateInterval,
    \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Query\ResultSetMapping,
    \App\Services\Halt,
    \App\Entities\Upload;

class UploadWrapper
{
    protected $em;
    protected $time;

    const LIST_LIMIT = 5;

    const FILE_DIR = 'files/';
    const FILE_SIZE = '10M'; // max size of one file
    const FILE_MIMES = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff', 'image/webp', 'application/pdf']; // available mimes: http://www.iana.org/assignments/media-types/media-types.xhtml

    const THUMB_DIR = 'thumbs/';
    const THUMB_WIDTH = 240;
    const THUMB_FORMAT = 'jpeg';
    const THUMB_MIMES = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff', 'image/webp'];

    public function __construct(\Doctrine\ORM\EntityManager $em, \App\Services\Time $time) {
        $this->em = $em;
        $this->time = $time;
    }

    public function __set(string $key, mixed $value) {
        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

    public function __get(string $key) {
        return property_exists($this, $key) ? $this->$key : null;
    }

    public function __isset(string $key) {
        return property_exists($this, $key) ? !empty($this->$key) : false;
    }

    public function insert(int $comment_id, int $user_id, array $files) {

        if(empty($files)) {
            Halt::throw(2125); // file read failed
        }

        /*
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
            throw new AppException('Upload limit exceeded', 216);
        }
        */

        // -- Original file --
        $file_dir = self::FILE_DIR . date('Y-m-d');
        if(!file_exists($file_dir)) {
            try {
                mkdir($file_dir, 0777, true);

            } catch (\Exception $e) {
                Halt::throw(2123); // directory make failed
            }
        }

        $file_key = array_key_first($files);
        $file = new \Upload\File($file_key, new \Upload\Storage\FileSystem($file_dir));
        $file->addValidations([new \Upload\Validation\Mimetype(self::FILE_MIMES), new \Upload\Validation\Size(self::FILE_SIZE)]);
        $file->setName(uniqid());

        // -- Thumb file --
        if(in_array($file->getMimetype(), self::THUMB_MIMES)) {

            $thumb = new \phpThumb();
            $thumb->setSourceFilename($files[$file_key]['tmp_name']);
            $thumb->setParameter('w', self::THUMB_WIDTH);
            $thumb->setParameter('config_output_format', self::THUMB_FORMAT);
            $thumb->setParameter('config_allow_src_above_docroot', true);

            $thumb_dir = self::THUMB_DIR . date('Y-m-d');
            $thumb_file = $thumb_dir . '/' . uniqid() . '.' . $thumb->config_output_format;

            if(!file_exists($thumb_dir)) {
                try {
                    mkdir($thumb_dir, 0777, true);

                } catch (\Exception $e) {
                    Halt::throw(2123); // directory make failed
                }
            }

            if ($thumb->GenerateThumbnail()) { 
                if (!$thumb->RenderToFile(__DIR__ . '/../../public/' . $thumb_file)) {
                    $thumb_file = null;
                }
            } else {
                $thumb_file = null;
            }
        }

        // -- Upload --
        $upload = new Upload();
        $upload->create_date = $this->time->datetime;
        $upload->update_date = new DateTime('1970-01-01 00:00:00');
        $upload->user_id = $user_id;
        $upload->comment_id = $comment_id;
        $upload->upload_name = $files[$file_key]['name'];
        $upload->upload_file = $file_dir . '/' . $file->getNameWithExtension();
        $upload->upload_mime = $file->getMimetype();
        $upload->upload_size = $file->getSize();
        $upload->thumb_file = !empty($thumb_file) ? $thumb_file : null;

        try {
            $file->upload();
            $this->em->persist($upload);
            $this->em->flush();

        } catch (\Exception $e) {
            Halt::throw(2126); // file write failed
        }

        return $upload;
    }

    public function select(int $upload_id) {

        $upload = $this->em->getRepository('\App\Entities\Upload')->find($upload_id);

        if(empty($upload)) {
            Halt::throw(2101); // upload not found
        }

        return $upload;
    }

    public function update(\App\Entities\Upload $upload, \App\Entities\UserRole $role, string $upload_name) {

        if($upload->user_id != $role->user_id and $role->role_status != 'admin') {
            Halt::throw(2102); // upload action denied
        }

        $upload->update_date = $this->time->datetime;
        $upload->upload_name = $upload_name;
        $this->em->persist($upload);
        $this->em->flush();
        return $upload;
    }

    public function delete(\App\Entities\Upload $upload, \App\Entities\UserRole $role) {

        if($upload->user_id != $role->user_id and $role->role_status != 'admin') {
            Halt::throw(2102); // upload action denied
        }

        // -- Original file --
        if(file_exists($upload->upload_file)) {
            try {
                unlink($upload->upload_file);

            } catch (\Exception $e) {
                Halt::throw(2127); // file delete failed
            }
        }

        // -- Thumb file --
        if(!empty($upload->thumb_file) and file_exists($upload->thumb_file)) {
            try {
                unlink($upload->thumb_file);

            } catch (\Exception $e) {
                Halt::throw(2127); // file delete failed
            }
        }

        $this->em->remove($upload);
        $this->em->flush();
    }

    public function list(int $comment_id) {

        $qb1 = $this->em->createQueryBuilder();

        $qb1->select('upload.id')->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->eq('upload.comment_id', $comment_id, Type::INTEGER))
            ->orderBy('upload.id', 'DESC');

        $uploads = array_map(fn($n) => $this->em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());
        return $uploads;
    }

}
