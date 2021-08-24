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

class RepoWrapper
{
    protected $em;

    const REPO_INSERT_LIMIT = 20; // maximum repo number per user
    const REPO_LIST_LIMIT = 5; // number of results in list

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

    public function insert(string $user_token, string $repo_name) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Filter: repos number per user --
        $stmt = $this->em->getConnection()->prepare("SELECT COUNT(id) FROM repos WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user->id, Type::INTEGER);
        $stmt->execute();
        $repos_count = $stmt->fetchOne();

        if($repos_count >= self::REPO_INSERT_LIMIT) {
            throw new AppException('Repository limit exceeded', 206);
        }

        // -- Insert repo --
        $repo = new Repo();
        $repo->create_date = Flight::datetime();
        $repo->update_date = new DateTime('1970-01-01 00:00:00');
        $repo->user_id = $user->id;
        $repo->repo_name = $repo_name;
        $this->em->persist($repo);
        $this->em->flush();

        // -- Insert user role --
        $user_role = new UserRole();
        $user_role->create_date = Flight::datetime();
        $user_role->update_date = new DateTime('1970-01-01 00:00:00');
        $user_role->user_id = $user->id;
        $user_role->repo_id = $repo->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->repo = $repo;
        $this->em->persist($user_role);
        $this->em->flush();

        // -- Clear cache: user terms --
        foreach($user->user_terms->getValues() as $term) {
            if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
            }
        }

        // -- End --
        Flight::json([
            'success' => 'true',
            'repo' => [
                'id' => $repo->id
            ]
        ]);
    }

    public function select(string $user_token, string $repo_id) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $user_role->repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        // -- Repo alerts --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('alerts_count', 'alerts_count');

        $query = $this->em
            ->createNativeQuery("SELECT alerts_count FROM vw_repos_alerts WHERE user_id = :user_id AND repo_id = :repo_id", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('repo_id', $repo->id);
        $query_result = $query->getResult();
        $alerts_count = !empty($query_result[0]) ? $query_result[0]['alerts_count'] : 0;

        // -- End --
        Flight::json([
            'success' => 'true',

            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,

                'repo_alerts' => [
                    'alerts_count' => $alerts_count
                ],
    
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

        ]);
    }

    public function update(string $user_token, int $repo_id, string $repo_name) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- User role --
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('Role rights are not enough', 208);
        }

        // -- Repo --
        $repo = $this->em->find('App\Entities\Repo', $user_role->repo_id);

        if(empty($repo)) {
            throw new AppException('Repository not found', 205);
        }

        $repo->update_date = Flight::datetime();
        $repo->repo_name = $repo_name;
        $this->em->persist($repo);
        $this->em->flush();

        // -- End --
        Flight::json([
            'success' => 'true'
        ]);
    }

    public function delete(string $user_token, string $repo_id) {

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
        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo_id, 'user_id' => $user->id]);

        if(empty($user_role)) {
            throw new AppException('Role not found', 207);

        } elseif($user_role->role_status != 'admin') {
            throw new AppException('Role rights are not enough', 208);
        }

        // -- Uploads --
        $qb3 = $this->em->createQueryBuilder();
        $qb3->select('post.id')
            ->from('App\Entities\Post', 'post')
            ->where($qb3->expr()->eq('post.repo_id', $repo->id));

        $qb2 = $this->em->createQueryBuilder();
        $qb2->select('comment.id')
            ->from('App\Entities\Comment', 'comment')
            ->where($qb2->expr()->in('comment.post_id', $qb3->getDQL()));

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->in('upload.comment_id', $qb2->getDQL()));

        $uploads = array_map(fn($n) => $this->em->find('App\Entities\Upload', $n['id']), $qb1->getQuery()->getResult());

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

        // -- Members --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('role.user_id')
            ->from('App\Entities\UserRole', 'role')
            ->where($qb1->expr()->eq('role.repo_id', $repo->id));

        $members = array_map(fn($n) => $this->em->find('App\Entities\User', $n['user_id']), $qb1->getQuery()->getResult());

        // -- Delete repo --
        $this->em->remove($repo);
        $this->em->flush();

        // -- Clear cache: user terms --
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

    public function list(string $user_token, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Repos --
        $qb2 = $this->em->createQueryBuilder();

        $qb2->select('role.repo_id')
            ->from('App\Entities\UserRole', 'role')
            ->where($qb2->expr()->eq('role.user_id', $user->id));

        $qb1 = $this->em->createQueryBuilder();
        $qb1->select(['repo.id'])
            ->from('App\Entities\Repo', 'repo')
            ->where($qb1->expr()->in('repo.id', $qb2->getDQL()))
            ->orderBy('repo.repo_name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::REPO_LIST_LIMIT);

        $repos = array_map(fn($n) => $this->em->find('App\Entities\Repo', $n['id']), $qb1->getQuery()->getResult());

        // -- End --
        Flight::json([
            'success' => 'true',

            'repos_limit' => self::REPO_LIST_LIMIT,
            'repos_count' => (int) call_user_func( 
                function($terms, $key) {
                    $tmp = $terms->filter(function($el) use ($key) {
                        return $el->term_key == $key;
                    })->first();
                    return empty($tmp) ? 0 : $tmp->term_value;
                }, $user->user_terms, 'roles_count' ),

            'repos'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'repo_name' => $n->repo_name,
                'user_id' => $n->user_id,

                'repo_alerts' => [
                    'alerts_count' => (int) call_user_func(
                        function($user_id, $repo_id) {
                            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
                            $rsm->addScalarResult('alerts_count', 'alerts_count');
                            $query = $this->em
                                ->createNativeQuery("SELECT alerts_count FROM vw_repos_alerts WHERE user_id = :user_id AND repo_id = :repo_id", $rsm)
                                ->setParameter('user_id', $user_id)
                                ->setParameter('repo_id', $repo_id);
                            $query_result = $query->getResult();
                            return !empty($query_result[0]) ? $query_result[0]['alerts_count'] : 0;
                    }, $user->id, $n->id)
                ],

                'repo_terms' => call_user_func( 
                    function($repo_terms) {
                        return array_combine(
                            array_map(fn($m) => $m->term_key, $repo_terms), 
                            array_map(fn($m) => $m->term_value, $repo_terms));
                    }, $n->repo_terms->toArray()),

                'user_role' => call_user_func(
                    function($repo_id, $user_id) {
                        $user_role = $this->em->getRepository('\App\Entities\UserRole')->findOneBy(['repo_id' => $repo_id, 'user_id' => $user_id]);
                        return [
                            'id' => $user_role->id,
                            'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                            'user_id' =>$user_role->user_id,
                            'repo_id' =>$user_role->repo_id,
                            'role_status' => $user_role->role_status
                        ];
                    }, $n->id, $n->user_id
                ),

            ], $repos)
        ]);
    }

}
