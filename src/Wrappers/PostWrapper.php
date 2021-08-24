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
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- Filter: posts number per repo --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM posts WHERE repo_id = :repo_id");
        $stmt->bindValue(':repo_id', $repo->id, Type::INTEGER);
        $stmt->execute();
        $posts_count = $stmt->fetchOne();

        if($posts_count >= self::POST_INSERT_LIMIT) {
            throw new AppException('Post limit exceeded', 212);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif(!in_array($user_role->role_status, ['admin', 'editor'])) {
            throw new AppException('Role rights are not enough', 208);
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

        // -- Post alerts --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $query = $this->em
            ->createNativeQuery("SELECT alerts_count FROM vw_posts_alerts WHERE user_id = :user_id AND post_id = :post_id", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('post_id', $post->id);
        $query_result = $query->getResult();
        $alerts_count = !empty($query_result[0]) ? $query_result[0]['alerts_count'] : 0;

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

                'post_alerts' => [
                    'alerts_count' => $alerts_count
                ],

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

        } elseif($user_role->role_status != 'admin' and !($post->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('Action prohibited', 102);
        }

        // -- Post --
        $post->update_date = Flight::datetime();
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

        } elseif($user_role->role_status != 'admin' and !($post->user_id == $user->id and $user_role->role_status == 'editor')) {
            throw new AppException('Action prohibited', 102);
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
            if(file_exists($upload->upload_file)) {
                try {
                    unlink($upload->upload_file);

                } catch (\Exception $e) {
                    throw new AppException('File delete failed', 107);
                }
            }

            // -- Thumb file --
            if(!empty($upload->thumb_file) and file_exists($upload->thumb_file)) {
                try {
                    unlink($upload->thumb_file);

                } catch (\Exception $e) {
                    throw new AppException('File delete failed', 107);
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

    public function list(string $user_token, int $repo_id, string $post_status, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo->id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);
        }

        $qb1 = $this->em->createQueryBuilder();

        $qb1->select('post.id')->from('App\Entities\Post', 'post')
            ->where($qb1->expr()->eq('post.repo_id', $repo->id))
            ->andWhere($qb1->expr()->eq('post.post_status', $this->em->getConnection()->quote($post_status, \Doctrine\DBAL\ParameterType::STRING)))
            ->orderBy('post.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(self::POST_LIST_LIMIT);

        $posts_count = call_user_func(
            function($terms, $post_status) {

                $tmp = $terms->filter(function($el) use ($post_status) {
                    return $el->term_key == $post_status . '_count';
                })->first();
                return empty($tmp) ? 0 : $tmp->term_value;

            }, $repo->repo_terms, $post_status
        );

        $posts = array_map(fn($n) => $this->em->find('App\Entities\Post', $n['id']), $qb1->getQuery()->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,
    
                'repo_terms' => call_user_func( 
                    function($repo_terms) {
                        return array_combine(
                            array_map(fn($n) => $n->term_key, $repo_terms), 
                            array_map(fn($n) => $n->term_value, $repo_terms));
                    }, $repo->repo_terms->toArray()),

                'user_role' => [
                    'id' => $user_role->id,
                    'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $user_role->user_id,
                    'repo_id' => $user_role->repo_id,
                    'role_status' => $user_role->role_status,
                ]
            ],

            'posts_limit' => self::POST_LIST_LIMIT,
            'posts_count' => (int) $posts_count,

            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'post_status' => $n->post_status,
                'post_title' => $n->post_title,

                'post_alerts' => [
                    'alerts_count' => (int) call_user_func(
                        function($user_id, $post_id) {
                            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
                            $rsm->addScalarResult('alerts_count', 'alerts_count');
                            $query = $this->em
                                ->createNativeQuery("SELECT alerts_count FROM vw_posts_alerts WHERE user_id = :user_id AND post_id = :post_id", $rsm)
                                ->setParameter('user_id', $user_id)
                                ->setParameter('post_id', $post_id);
                            $query_result = $query->getResult();
                            return !empty($query_result[0]) ? $query_result[0]['alerts_count'] : 0;
                    }, $user->id, $n->id)
                ],

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

            ], $posts)
        ]);
    }

    public function bytag(string $user_token, string $post_tag, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

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

        $qc1_result = $qc1->getQuery()->getResult();
        $posts_count = $qc1_result[0][1];
        $posts = array_map(fn($n) => $this->em->find('App\Entities\Post', $n['id']), $qb1->getQuery()->getResult());


        // -- End --
        Flight::json([
            'success' => 'true',

            'posts_limit' => self::POST_LIST_LIMIT,
            'posts_count' => (int) $posts_count,

            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'post_status' => $n->post_status,
                'post_title' => $n->post_title,

                'post_alerts' => [
                    'alerts_count' => (int) call_user_func(
                        function($user_id, $post_id) {
                            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
                            $rsm->addScalarResult('alerts_count', 'alerts_count');
                            $query = $this->em
                                ->createNativeQuery("SELECT alerts_count FROM vw_posts_alerts WHERE user_id = :user_id AND post_id = :post_id", $rsm)
                                ->setParameter('user_id', $user_id)
                                ->setParameter('post_id', $post_id);
                            $query_result = $query->getResult();
                            return !empty($query_result[0]) ? $query_result[0]['alerts_count'] : 0;
                    }, $user->id, $n->id)
                ],

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

                'repo' => call_user_func(
                    function($repo_id) {
                        $repo = $this->em->find('\App\Entities\Repo', $repo_id);
                        return [
                            'id' => $repo->id, 
                            'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                            'user_id' => $repo->user_id,
                            'repo_name' => $repo->repo_name
                        ];
                    }, $n->repo_id
                ),

            ], $posts)
        ]);
    }

    public function bytitle(string $user_token, string $post_title, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Posts --

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('post_id', 'post_id');
        $rsm->addScalarResult('repo_id', 'repo_id');
        $rsm->addScalarResult('upload_id', 'upload_id');

        $query = $this->em
            ->createNativeQuery("SELECT post_id, repo_id, upload_id FROM vw_users_uploads WHERE user_id = :user_id OFFSET :offset LIMIT :limit", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('offset', $offset)
            ->setParameter('limit', self::UPLOAD_LIST_LIMIT);
        $query_result = $query->getResult();
        $uploads = !empty($query_result) ? $query_result : [];

        // -- Uploads count --
        $uploads_count = call_user_func(
            function($terms) {

                $tmp = $terms->filter(function($el) {
                    return $el->term_key == 'uploads_count';
                })->first();
                return empty($tmp) ? 0 : $tmp->term_value;

            }, $user->user_terms
        );


        /*
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

        $qc1_result = $qc1->getQuery()->getResult();
        $posts_count = $qc1_result[0][1];
        $posts = array_map(fn($n) => $this->em->find('App\Entities\Post', $n['id']), $qb1->getQuery()->getResult());
        */


        // -- End --
        Flight::json([
            'success' => 'true',

            'posts_limit' => self::POST_LIST_LIMIT,
            'posts_count' => (int) $posts_count,

            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'post_status' => $n->post_status,
                'post_title' => $n->post_title,

                'post_alerts' => [
                    'alerts_count' => (int) call_user_func(
                        function($user_id, $post_id) {
                            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
                            $rsm->addScalarResult('alerts_count', 'alerts_count');
                            $query = $this->em
                                ->createNativeQuery("SELECT alerts_count FROM vw_posts_alerts WHERE user_id = :user_id AND post_id = :post_id", $rsm)
                                ->setParameter('user_id', $user_id)
                                ->setParameter('post_id', $post_id);
                            $query_result = $query->getResult();
                            return !empty($query_result[0]) ? $query_result[0]['alerts_count'] : 0;
                    }, $user->id, $n->id)
                ],

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

                'repo' => call_user_func(
                    function($repo_id) {
                        $repo = $this->em->find('\App\Entities\Repo', $repo_id);
                        return [
                            'id' => $repo->id, 
                            'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                            'user_id' => $repo->user_id,
                            'repo_name' => $repo->repo_name
                        ];
                    }, $n->repo_id
                ),

            ], $posts)
        ]);
    }

}
