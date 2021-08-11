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

class PostWrapper
{
    protected $em;

    const POST_INSERT_LIMIT = 512; // maximum posts numbers per one repo
    const POST_LIST_LIMIT = 5;

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

    public function insert(string $user_token, int $repo_id, string $post_status, string $post_title, string $post_tags) {

        $post_tags = explode(',', $post_tags);
        $post_tags = array_map(fn($value) => trim(mb_strtolower($value)) , $post_tags);
        $post_tags = array_filter($post_tags, fn($value) => !empty($value));
        $post_tags = array_unique($post_tags);

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('repo not found', 0);
        }

        // -- Filter: posts number per repo --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM posts WHERE repo_id = :repo_id");
        $stmt->bindValue(':repo_id', $repo->id, Type::INTEGER);
        $stmt->execute();
        $posts_count = $stmt->fetchOne();

        if($posts_count >= self::POST_INSERT_LIMIT) {
            throw new AppException('posts limit exceeded', 0);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('role not found', 0);

        } elseif(!in_array($user_role->role_status, ['admin', 'editor'])) {
            throw new AppException('role_status must be admin or editor', 0);
        }

        // -- Post --
        $post = new Post();
        $post->create_date = Flight::datetime();
        $post->update_date = new DateTime('1970-01-01 00:00:00');
        $post->user_id = $user->id;
        $post->repo_id = $repo->id;
        $post->post_status = $post_status;
        $post->post_title = $post_title;
        $this->em->persist($post);
        $this->em->flush();

        // -- Tags --
        foreach($post_tags as $post_tag) {
            $tag = new PostTag();
            $tag->create_date = Flight::datetime();
            $tag->update_date = new DateTime('1970-01-01 00:00:00');
            $tag->post_id = $post->id;
            $tag->tag_value = $post_tag;
            $tag->post = $post;
            $this->em->persist($tag);
            $this->em->flush();
        }

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'post' => [
                'id' => $post->id
            ]
        ]);
    }

    public function select(string $user_token, int $post_id) {

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

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('repo not found', 0);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('role not found', 0);
        }

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

                'user'=> call_user_func(
                    function($user_id) {
                        $member = $this->em->find('App\Entities\User', $user_id);
                        return [
                            'id' => $member->id,
                            'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                            'user_status' => $member->user_status,
                            'user_name' => $member->user_name
                        ];
                    }, $post->user_id)

            ]
        ]);
    }

    public function update(string $user_token, int $post_id, string $post_status, string $post_title, string $post_tags) {

        $post_tags = explode(',', $post_tags);
        $post_tags = array_map(fn($value) => trim(mb_strtolower($value)) , $post_tags);
        $post_tags = array_filter($post_tags, fn($value) => !empty($value));
        $post_tags = array_unique($post_tags);

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

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('repo not found', 0);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('role not found', 0);

        } elseif($user_role->role_status != 'admin' and !($post->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('permission denied', 0);
        }

        // -- Post --
        $post->post_status = $post_status;
        $post->post_title = $post_title;
        $this->em->persist($post);
        $this->em->flush();

        // -- Update tags --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('tag.id')->from('App\Entities\postTag', 'tag')->where($qb1->expr()->eq('tag.post_id', $post->id));
        $tags = array_map(fn($n) => $this->em->find('App\Entities\PostTag', $n['id']), $qb1->getQuery()->getResult());

        foreach($tags as $tag) {
            $this->em->remove($tag);
            $this->em->flush();
        }

        foreach($post_tags as $post_tag) {
            $tag = new PostTag();
            $tag->create_date = Flight::datetime();
            $tag->update_date = new DateTime('1970-01-01 00:00:00');
            $tag->post_id = $post->id;
            $tag->tag_value = $post_tag;
            $tag->post = $post;
            $this->em->persist($tag);
            $this->em->flush();
        }

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

    public function delete(string $user_token, int $post_id) {

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

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $post->repo_id);

        if(empty($repo)) {
            throw new AppException('repo not found', 0);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('role not found', 0);

        } elseif($user_role->role_status != 'admin' and !($post->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('permission denied', 0);
        }

        // -- Uploads --
        $qb2 = $this->em->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->eq('comment.post_id', $post->id));

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads = array_map(fn($n) => $em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

        // -- Files --
        foreach($uploads as $upload) {
            
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
        }

        // -- Post delete --
        $this->em->remove($post);
        $this->em->flush();

        // -- Clear cache: repo terms --
        foreach($repo->repo_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\RepoTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\RepoTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([ 
            'success' => 'true'
        ]);
    }

    public function list(string $user_token, int $repo_id, string $post_status, string $post_title, string $post_tag, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('user not found', 0);

        } elseif($user->user_status == 'trash') {
            throw new AppException('user_status is trash', 0);
        }

        // -- Posts --
        if(!empty($repo_id) and !empty($post_status)) {

            // -- Repo --
            $repo = $this->em->find('App\Entities\Repo', $repo_id);

            if(empty($repo)) {
                throw new AppException('repo not found', 0);
            }

            // -- User role --
            $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

            if(empty($user_role)) {
                throw new AppException('role not found', 0);
            }

            $qb1 = $this->em->createQueryBuilder();
            $qc1 = $this->em->createQueryBuilder();

            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.repo_id', $repo->id))
                ->andWhere($qb1->expr()->eq('post.post_status', $this->em->getConnection()->quote($post_status, \Doctrine\DBAL\ParameterType::STRING)))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(self::POST_LIST_LIMIT);

            $qc1->select('count(post.id)')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->eq('post.repo_id', $repo->id))
                ->andWhere($qb1->expr()->eq('post.post_status', $this->em->getConnection()->quote($post_status, \Doctrine\DBAL\ParameterType::STRING)));

        } elseif(!empty($post_title)) {

            $qb2 = $this->em->createQueryBuilder();
            $qb1 = $this->em->createQueryBuilder();
            $qc1 = $this->em->createQueryBuilder();

            $qb2->select('role.repo_id')
                ->from('App\Entities\UserRole', 'role')
                ->where($qb2->expr()->eq('role.user_id', $user->id));

            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->in('post.repo_id', $qb2->getDQL()))
                ->andWhere($qb1->expr()->like('post.post_title', "'%" . $post_title . "%'", $this->em->getConnection()->quote($post_title, \Doctrine\DBAL\ParameterType::STRING)))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(self::POST_LIST_LIMIT);

            $qc1->select('count(post.id)')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->in('post.repo_id', $qb2->getDQL()))
                ->andWhere($qb1->expr()->like('post.post_title', "'%" . $post_title . "%'", $this->em->getConnection()->quote($post_title, \Doctrine\DBAL\ParameterType::STRING)));

        } elseif(!empty($post_tag)) {

            $qb3 = $this->em->createQueryBuilder();
            $qb2 = $this->em->createQueryBuilder();
            $qb1 = $this->em->createQueryBuilder();
            $qc1 = $this->em->createQueryBuilder();

            $qb3->select('role.repo_id')
                ->from('App\Entities\UserRole', 'role')
                ->where($qb2->expr()->eq('role.user_id', $user->id));

            $qb2->select('tag.post_id')
                ->from('App\Entities\PostTag', 'tag')
                ->where($qb2->expr()->eq('tag.tag_value', "'" . $post_tag . "'", $this->em->getConnection()->quote($post_tag, \Doctrine\DBAL\ParameterType::STRING)));
    
            $qb1->select('post.id')->from('App\Entities\Post', 'post')
                ->where($qb1->expr()->in('post.repo_id', $qb3->getDQL()))
                ->andWhere($qb1->expr()->in('post.id', $qb2->getDQL()))
                ->orderBy('post.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults(self::POST_LIST_LIMIT);

            $qc1->select('count(post.id)')->from('App\Entities\Post', 'post')
                ->where($qc1->expr()->in('post.repo_id', $qb3->getDQL()))
                ->andWhere($qc1->expr()->in('post.id', $qb2->getDQL()));

        } else {
            throw new AppException('posts not found', 0);
        }

        $posts = array_map(fn($n) => $this->em->find('App\Entities\Post', $n['id']), $qb1->getQuery()->getResult());
        $posts_count = $qc1->getQuery()->getResult();

        // -- End --
        Flight::json([
            'success' => 'true',

            'posts_limit' => self::POST_LIST_LIMIT,
            'posts_count' => $posts_count[0][1],

            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'post_status' => $n->post_status,
                'post_title' => $n->post_title,

                'post_terms' => call_user_func( 
                    function($post_terms) {
                        return array_combine(
                            array_map(fn($m) => $m->term_key, $post_terms), 
                            array_map(fn($m) => $m->term_value, $post_terms));
                    }, $n->post_terms->toArray()),
    
                'post_tags' => call_user_func( 
                    function($post_tags) {
                        return array_map(fn($m) => $m->tag_value, $post_tags);
                    }, $n->post_tags->toArray()),

                'post_alerts' => call_user_func( 
                    function($post_alerts) {
                        return array_combine(
                            array_map(fn($m) => $m->user_id, $post_alerts), 
                            array_map(fn($m) => $m->alerts_count, $post_alerts));
                    }, $n->post_alerts->toArray()),

            ], $posts)
        ]);
    }

}
