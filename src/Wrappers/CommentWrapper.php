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

class CommentWrapper
{
    protected $em;

    const COMMENT_INSERT_LIMIT = 2048; // maximum comments number per one post
    const COMMENT_LIST_LIMIT = 5;

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

    public function insert(string $user_token, int $post_id, string $comment_content) {
 
        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Post --
        $post = $this->em->find('App\Entities\Post', $post_id);

        if(empty($post)) {
            throw new AppException('Post not found', 211);
        }

        // -- Filter: comments number per post --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM comments WHERE post_id = :post_id");
        $stmt->bindValue(':post_id', $post->id, Type::INTEGER);
        $stmt->execute();
        $comments_count = $stmt->fetchOne();

        if($comments_count >= self::COMMENT_INSERT_LIMIT) {
            throw new AppException('Comment limit exceeded', 214);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif(!in_array($user_role->role_status, ['admin', 'editor'])) {
            throw new AppException('permission denied', 0);
        }

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = Flight::datetime();
        $comment->update_date = new DateTime('1970-01-01 00:00:00');
        $comment->user_id = $user->id;
        $comment->post_id = $post->id;
        $comment->comment_content = $comment_content;
        $comment->post = $post;
        $this->em->persist($comment);
        $this->em->flush();

        // -- Clear cache: post terms --
        foreach($post->post_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\PostTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\PostTerm', $term->id);
            }
        }

        // -- Members --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('role.user_id')
            ->from('App\Entities\UserRole', 'role')
            ->where($qb1->expr()->eq('role.repo_id', $repo->id));

        $members = array_map(fn($n) => $this->em->find('App\Entities\User', $n['user_id']), $qb1->getQuery()->getResult());

        // -- Clear cache: users terms --
        foreach($members as $member) {
            foreach($member->user_terms->getValues() as $term) {
                if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                    $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
                }
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'comment' => [
                'id' => $comment->id
            ]
        ]);
    }

    public function update(string $user_token, int $comment_id, string $comment_content) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Comment --
        $comment = $this->em->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('Comment not found', 213);
        }

        // -- Post --
        $post = $this->em->find('App\Entities\Post', $comment->post_id);

        if(empty($post)) {
            throw new AppException('Post not found', 211);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin' and !($comment->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('Action prohibited', 102);
        }

        // -- Update comment --
        $comment->update_date = Flight::datetime();
        $comment->comment_content = $comment_content;
        $this->em->persist($comment);
        $this->em->flush();

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

    public function delete(string $user_token, int $comment_id) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Comment --
        $comment = $this->em->find('App\Entities\Comment', $comment_id);

        if(empty($comment)) {
            throw new AppException('Comment not found', 213);
        }

        // -- Post --
        $post = $this->em->find('App\Entities\Post', $comment->post_id);

        if(empty($post)) {
            throw new AppException('Post not found', 211);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin' and !($comment->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('Action prohibited', 102);
        }

        // -- Uploads --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->eq('upload.comment_id', $comment->id));

        $uploads = array_map(fn($n) => $this->em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        // -- Files --
        foreach($uploads as $upload) {
            
            // -- Original file --
            if(file_exists($upload->upload_path)) {
                try {
                    unlink($upload->upload_path);

                } catch (\Exception $e) {
                    throw new AppException('File delete failed', 107);
                }
            }

            // -- Thumb file --
            if(!empty($upload->thumb_path) and file_exists($upload->thumb_path)) {
                try {
                    unlink($upload->thumb_path);

                } catch (\Exception $e) {
                    throw new AppException('File delete failed', 107);
                }
            }
        }

        // -- Delete comment --
        $this->em->remove($comment);
        $this->em->flush();

        // -- Clear cache: post terms --
        foreach($post->post_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\PostTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\PostTerm', $term->id);
            }
        }

        // -- Members --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('role.user_id')
            ->from('App\Entities\UserRole', 'role')
            ->where($qb1->expr()->eq('role.repo_id', $repo->id));

        $members = array_map(fn($n) => $this->em->find('App\Entities\User', $n['user_id']), $qb1->getQuery()->getResult());

        // -- Clear cache: users terms --
        foreach($members as $member) {
            foreach($member->user_terms->getValues() as $term) {
                if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                    $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
                }
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

    public function list(string $user_token, int $post_id, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Post --
        $post = $this->em->find('App\Entities\Post', $post_id);

        if(empty($post)) {
            throw new AppException('Post not found', 211);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);
        }

        // -- Comments --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('comment.id')->from('App\Entities\Comment', 'comment')
            ->where($qb1->expr()->eq('comment.post_id', $post->id))
            ->orderBy('comment.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::COMMENT_LIST_LIMIT);
        $comments = array_map(fn($n) => $this->em->find('App\Entities\Comment', $n['id']), $qb1->getQuery()->getResult());

        /*
        // -- Delete alerts --
        $post_alert = $this->em->getRepository('\App\Entities\PostAlert')->findOneBy(['user_id' => $user->id, 'post_id' => $post->id]);
        if(!empty($post_alert)) {
            $this->em->remove($post_alert);
            $this->em->flush();
        }
        */

        // -- End --
        Flight::json([
            'success' => 'true',

            'post' => [
                'id' => $post->id, 
                'create_date' => $post->create_date->format('Y-m-d H:i:s'),
                'user_id' => $post->user_id,
                'repo_id' => $post->repo_id,
                'post_status' => $post->post_status,
                'post_title' => $post->post_title,

                'post_terms' => call_user_func( 
                    function($post_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $post_terms), 
                            array_map(fn($n) => $n->term_value, $post_terms));
                    }, $post->post_terms->toArray()),
    
                'post_tags' => call_user_func( 
                    function($post_tags) {
                        return array_map(fn($n) => $n->tag_value, $post_tags);
                    }, $post->post_tags->toArray()),
            ],

            'comments_limit' => self::COMMENT_LIST_LIMIT,
            'comments_count' => (int) call_user_func( 
                function($terms) {
                    $tmp = $terms->filter(function($el) {
                        return $el->term_key == 'comments_count';
                    })->first();
                    return empty($tmp) ? 0 : $tmp->term_value;
                }, $post->post_terms),

            'comments'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'comment_content' => $n->comment_content,

                'comment_uploads' => array_map(fn($m) => [
                    'id' => $m->id,
                    'create_date' => $m->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $m->user_id,
                    'comment_id' => $m->comment_id,
                    'upload_name' => $m->upload_name,
                    'upload_mime' => $m->upload_mime,
                    'upload_path' => $m->upload_path,
                    'upload_size' => $m->upload_size,
                    'thumb_path' => $m->thumb_path
                ], $n->comment_uploads->toArray())

            ], $comments)
        ]);
    }

}
