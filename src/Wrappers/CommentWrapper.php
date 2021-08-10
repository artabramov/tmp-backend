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
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Post --
        $post = $this->em->find('App\Entities\Post', $post_id);

        if(empty($post)) {
            throw new AppException('post not found', 0);
        }

        // -- Filter: comments number per post --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM comments WHERE post_id = :post_id");
        $stmt->bindValue(':post_id', $post->id, Type::INTEGER);
        $stmt->execute();
        $comments_count = $stmt->fetchOne();

        if($comments_count >= self::COMMENT_INSERT_LIMIT) {
            throw new AppException('comments limit exceeded', 0);
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

        // -- Update comment --
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

        // -- Uploads --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->eq('upload.comment_id', $comment->id));

        $uploads = array_map(fn($n) => $this->em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        // -- Files --
        foreach($uploads as $upload) {
            if(file_exists($upload->upload_path)) {
                unlink($upload->upload_path);
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

}
