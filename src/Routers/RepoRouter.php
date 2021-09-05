<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Services\Email,
    \App\Wrappers\UserWrapper,
    \App\Wrappers\UserTermWrapper,
    \App\Wrappers\RepoWrapper,
    \App\Wrappers\RepoTermWrapper,
    \App\Wrappers\RoleWrapper;

class RepoRouter
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

    public function insert(string $user_token, string $repo_name) {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->insert($user->id, $repo_name);

        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $role = $role_wrapper->insert($user->id, $repo->id, 'admin');

        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($user->id, 'roles_count');

        return [
            'success' => 'true',
            'repo' => [
                'id' => $repo->id
            ]
        ];
    }
    
    public function select(string $user_token, string $repo_id) {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo_id);

        // -- End --
        return [
            'success' => 'true',

            'repo' => [
                'id' => $repo->id, 
                'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                'user_id' => $repo->user_id,
                'repo_name' => $repo->repo_name,

                'user_role' => [
                    'id' => $user_role->id,
                    'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                    'user_id' => $user_role->user_id,
                    'repo_id' => $user_role->repo_id,
                    'role_status' => $user_role->role_status,
                ],

                'repo_terms' => (array) call_user_func(function($repo_id) {
                    $term_wrapper = new RepoTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($repo_id);
                    return array_combine(
                        array_map(fn($n) => $n->term_key, $terms), 
                        array_map(fn($n) => $n->term_value, $terms)
                    );
                }, $repo->id),

                /*
                'repo_alerts' => (int) Flight::count_alerts($repo, $user),
                */
            ],

        ];
    }

    public function update(string $user_token, int $repo_id, string $repo_name) {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo_id, 'admin');

        $repo = $repo_wrapper->update($repo, $repo_name);

        return [
            'success' => 'true'
        ];
    }

    public function delete(string $user_token, string $repo_id) {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($repo_id);

        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo_id, 'admin');

        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($user->id, 'roles_count');

        $repo_wrapper->delete($repo);

        /*
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

        foreach($members as $member) {
            foreach($member->user_terms->getValues() as $term) {
                if($this->em->getCache()->containsEntity('\App\Entities\UserTerm', $term->id)) {
                    $this->em->getCache()->evictEntity('\App\Entities\UserTerm', $term->id);
                }
            }
        }
        */

        // -- End --
        return [
            'success' => 'true'
        ];
    }

    public function list(string $user_token, int $offset) {

        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repos = $repo_wrapper->list($user->id, $offset);

        return [
            'success' => 'true',

            'repos_limit' => $repo_wrapper::LIST_LIMIT,
            'repos_count' => (int) call_user_func( 
                function($user_id) {
                    $term_wrapper = new UserTermWrapper($this->em, $this->time);
                    $term = $term_wrapper->select($user_id, 'roles_count');
                    return empty($term) ? 0 : $term->term_value;
                }, $user->id),

            'repos'=> array_map(fn($n) => [
                'id' => $n->id,
                'create_date' => $n->create_date->format('Y-m-d H:i:s'),
                'repo_name' => $n->repo_name,
                'user_id' => $n->user_id,

                'user_role' => call_user_func(
                    function($repo_id, $user_id) {
                        $role_wrapper = new RoleWrapper($this->em, $this->time);
                        $user_role = $role_wrapper->select($user_id, $repo_id);
                        return [
                            'id' => $user_role->id,
                            'create_date' => $user_role->create_date->format('Y-m-d H:i:s'),
                            'user_id' =>$user_role->user_id,
                            'repo_id' =>$user_role->repo_id,
                            'role_status' => $user_role->role_status
                        ];
                    }, $n->id, $user->id
                ),

                'repo_terms' => (array) call_user_func(function($repo_id) {
                    $term_wrapper = new RepoTermWrapper($this->em, $this->time);
                    $terms = $term_wrapper->list($repo_id);
                    return array_combine(
                        array_map(fn($m) => $m->term_key, $terms), 
                        array_map(fn($m) => $m->term_value, $terms)
                    );
                }, $n->id),

                /*
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
                */

            ], $repos)
        ];
    }

}
