<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Services\Email,
    \App\Wrappers\AlertWrapper,
    \App\Wrappers\UserWrapper,
    \App\Wrappers\UserTermWrapper,
    \App\Wrappers\RepoWrapper,
    \App\Wrappers\RepoTermWrapper,
    \App\Wrappers\RoleWrapper,
    \App\Wrappers\PostWrapper,
    \App\Wrappers\PostTermWrapper,
    \App\Wrappers\PostTagWrapper;

class PostRouter
{
    protected $em;
    protected $time;

    public function __construct($em, $time) {
        $this->em = $em;
        $this->time = $time;
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

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo_id, ['admin', 'editor']);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->insert($user->id, $repo_id, $post_status, $post_title);

        // post tags
        $tags_values = call_user_func(function($post_tags) {
            $post_tags = explode(',', $post_tags);
            $post_tags = array_map(fn($value) => trim(mb_strtolower($value)) , $post_tags);
            $post_tags = array_filter($post_tags, fn($value) => !empty($value));
            $post_tags = array_unique($post_tags);
            return $post_tags;
        }, $post_tags);

        $post_tag_wrapper = new PostTagWrapper($this->em, $this->time);
        $post_tag_wrapper->insert($post->id, $tags_values);

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, $post_status . '_count');

        return [ 
            'success' => 'true',
            'post' => [
                'id' => $post->id
            ]
        ];
    }

    public function update(string $user_token, int $post_id, string $post_status, string $post_title, string $post_tags) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->select($post_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($post->repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin', 'editor']);

        // update post
        $post = $post_wrapper->update($post, $user_role, $post_status, $post_title);

        // post tags
        $tags_values = call_user_func(function($post_tags) {
            $post_tags = explode(',', $post_tags);
            $post_tags = array_map(fn($value) => trim(mb_strtolower($value)) , $post_tags);
            $post_tags = array_filter($post_tags, fn($value) => !empty($value));
            $post_tags = array_unique($post_tags);
            return $post_tags;
        }, $post_tags);

        $post_tag_wrapper = new PostTagWrapper($this->em, $this->time);
        $post_tag_wrapper->update($post->id, $tags_values);

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, 'todo_count');
        $repo_term_wrapper->evict($repo->id, 'doing_count');
        $repo_term_wrapper->evict($repo->id, 'done_count');

        return [ 
            'success' => 'true'
        ];
    }

    public function select(string $user_token, int $post_id) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->select($post_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($post->repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id);

        return [
            'success' => 'true',

            'post' => [
                'id' => $post->id, 
                'create_date' => $post->create_date->format('Y-m-d H:i:s'),
                'user_id' => $post->user_id,
                'repo_id' => $post->repo_id,
                'post_status' => $post->post_status,
                'post_title' => $post->post_title,

                'post_terms' => (array) call_user_func(function($post_id) {
                    $term_wrapper = new PostTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($post_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $post->id),

                'post_tags' => (array) call_user_func(function($post_id) {
                    $tag_wrapper = new PostTagWrapper($this->em, $this->time);
                    $tags = $tag_wrapper->list($post_id);
                    return array_map(fn($n) => $n->tag_value, $tags);
                }, $post->id),

                'post_terms' => (array) call_user_func(function($post_id) {
                    $term_wrapper = new PostTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($post_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $post->id),

                'alerts_count' => (int) call_user_func(function($user_id, $post_id) {
                    $alert_wrapper = new AlertWrapper($this->em, $this->time);
                    $alerts_count = $alert_wrapper->ofpost($user_id, $post_id);
                    return $alerts_count;
                }, $user->id, $post->id),

                'user'=> call_user_func(
                    function($user_id) {
                        $user_wrapper = new UserWrapper($this->em, $this->time);
                        $member = $user_wrapper->select($user_id);
                        return [
                            'id' => $member->id,
                            'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                            'user_status' => $member->user_status,
                            'user_name' => $member->user_name
                        ];
                    }, $post->user_id)

            ]
        ];
    }

    public function delete(string $user_token, int $post_id) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->select($post_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($post->repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin', 'editor']);

        // delete post
        $post_status = $post->post_status;
        $post_wrapper->delete($post, $user_role);

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, $post_status . '_count');

        /*
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
        */


        return [ 
            'success' => 'true'
        ];
    }

    public function list(string $user_token, int $repo_id, string $post_status, int $offset) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id);

        // posts
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $posts = $post_wrapper->list($repo_id, $post_status, $offset);

        return [
            'success' => 'true',

            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,

                'repo_terms' => (array) call_user_func(function($repo_id) {
                    $term_wrapper = new RepoTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($repo_id);
                    return array_combine(
                        array_map(fn($m) => $m->term_key, $terms), 
                        array_map(fn($m) => $m->term_value, $terms)
                    );
                }, $repo->id),

                'user_role' => [
                    'id' => $user_role->id,
                    'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $user_role->user_id,
                    'repo_id' => $user_role->repo_id,
                    'role_status' => $user_role->role_status,
                ]
            ],

            'posts_limit' => $post_wrapper::LIST_LIMIT,

            'posts_count' => (int) call_user_func( 
                function($repo_id, $post_status) {
                    $term_wrapper = new RepoTermWrapper($this->em, $this->time);
                    $term = $term_wrapper->select($repo_id, $post_status . '_count');
                    return empty($term) ? 0 : $term->term_value;
                }, $repo->id, $post_status),

            'posts'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'user_id' => $n->user_id,
                'repo_id' => $n->repo_id,
                'post_status' => $n->post_status,
                'post_title' => $n->post_title,

                'alerts_count' => (int) call_user_func(function($user_id, $post_id) {
                    $alert_wrapper = new AlertWrapper($this->em, $this->time);
                    $alerts_count = $alert_wrapper->ofpost($user_id, $post_id);
                    return $alerts_count;
                }, $user->id, $n->id),

                'post_terms' => (array) call_user_func(function($post_id) {
                    $term_wrapper = new PostTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($post_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $n->id),

                'post_tags' => (array) call_user_func(function($post_id) {
                    $tag_wrapper = new PostTagWrapper($this->em, $this->time);
                    $tags = $tag_wrapper->list($post_id);
                    return array_map(fn($m) => $m->tag_value, $tags);
                }, $n->id),

            ], $posts)


        ];
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
