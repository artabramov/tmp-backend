<?php
namespace App\Routers;
use \App\Services\Halt,
    \App\Services\Email,
    \App\Wrappers\UserWrapper,
    \App\Wrappers\UserTermWrapper,
    \App\Wrappers\RepoWrapper,
    \App\Wrappers\RepoTermWrapper,
    \App\Wrappers\RoleWrapper,
    \App\Wrappers\PostWrapper,
    \App\Wrappers\PostTermWrapper,
    \App\Wrappers\CommentWrapper,
    \App\Wrappers\UploadWrapper;

class UploadRouter
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

    public function insert(string $user_token, int $comment_id, array $files) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // comment
        $comment_wrapper = new CommentWrapper($this->em, $this->time);
        $comment = $comment_wrapper->select($comment_id);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->select($comment->post_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($post->repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin', 'editor']);

        // TODO: user space
        //$user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        //$space_size = $user_term_wrapper->select($user_id, 'space_size');
        //$space_expires = $user_term_wrapper->select($user_id, 'space_expires');

        // upload
        $upload_wrapper = new UploadWrapper($this->em, $this->time);
        $upload = $upload_wrapper->insert($comment->id, $user->id, $files);

        // post cache
        $post_term_wrapper = new PostTermWrapper($this->em, $this->time);
        $post_term_wrapper->evict($post->id, 'uploads_count');
        $post_term_wrapper->evict($post->id, 'uploads_sum');

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, 'uploads_count');
        $repo_term_wrapper->evict($repo->id, 'uploads_sum');

        // user cache
        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($user->id, 'uploads_count');
        $user_term_wrapper->evict($user->id, 'uploads_sum');

        return [ 
            'success' => 'true',
            'upload' => [
                'id' => $upload->id
            ]
        ];
    }

    public function delete(string $user_token, int $upload_id) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // upload
        $upload_wrapper = new UploadWrapper($this->em, $this->time);
        $upload = $upload_wrapper->select($upload_id);

        // comment
        $comment_wrapper = new CommentWrapper($this->em, $this->time);
        $comment = $comment_wrapper->select($upload->comment_id);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->select($comment->post_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($post->repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin', 'editor']);

        // delete upload
        $upload = $upload_wrapper->delete($upload, $user_role);

        // post cache
        $post_term_wrapper = new PostTermWrapper($this->em, $this->time);
        $post_term_wrapper->evict($post->id, 'uploads_count');
        $post_term_wrapper->evict($post->id, 'uploads_sum');

        // repo cache
        $repo_term_wrapper = new RepoTermWrapper($this->em, $this->time);
        $repo_term_wrapper->evict($repo->id, 'uploads_count');
        $repo_term_wrapper->evict($repo->id, 'uploads_sum');

        // user cache
        $user_term_wrapper = new UserTermWrapper($this->em, $this->time);
        $user_term_wrapper->evict($user->id, 'uploads_count');
        $user_term_wrapper->evict($user->id, 'uploads_sum');

        return [ 
            'success' => 'true'
        ];
    }

    public function update(string $user_token, int $upload_id, string $upload_name) {

        // auth
        $user_wrapper = new UserWrapper($this->em, $this->time);
        $user = $user_wrapper->auth($user_token);

        // upload
        $upload_wrapper = new UploadWrapper($this->em, $this->time);
        $upload = $upload_wrapper->select($upload_id);

        // comment
        $comment_wrapper = new CommentWrapper($this->em, $this->time);
        $comment = $comment_wrapper->select($upload->comment_id);

        // post
        $post_wrapper = new PostWrapper($this->em, $this->time);
        $post = $post_wrapper->select($comment->post_id);

        // repo
        $repo_wrapper = new RepoWrapper($this->em, $this->time);
        $repo = $repo_wrapper->select($post->repo_id);

        // user role
        $role_wrapper = new RoleWrapper($this->em, $this->time);
        $user_role = $role_wrapper->select($user->id, $repo->id, ['admin', 'editor']);

        // update upload
        $upload = $upload_wrapper->update($upload, $user_role, $upload_name);

        return [ 
            'success' => 'true'
        ];
    }

    public function list(string $user_token, int $offset) {

        // -- User auth --
        $user = $this->em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User not found', 201);

        } elseif($user->user_status == 'trash') {
            throw new AppException('User deleted', 202);
        }

        // -- Uploads --
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
        // -- Uploads count --
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('uploads_count', 'uploads_count');

        $query = $this->em
            ->createNativeQuery("SELECT COUNT(upload_id) AS uploads_count FROM vw_users_uploads WHERE user_id = :user_id", $rsm)
            ->setParameter('user_id', $user->id);
        $query_result = $query->getResult();
        $uploads_count = !empty($query_result) ? $query_result[0]['uploads_count'] : 0;
        */

        // -- End --
        Flight::json([
            'success' => 'true',

            'uploads_count' => $uploads_count,
            'uploads_limit' => self::UPLOAD_LIST_LIMIT,

            'uploads' => array_map(fn($n) => 
                call_user_func(function($n) {
                    $upload = $this->em->find('App\Entities\Upload', $n['upload_id']);
                    $repo = $this->em->find('App\Entities\Repo', $n['repo_id']);
                    $post = $this->em->find('App\Entities\Post', $n['post_id']);
                    return [
                        'id' => $upload->id,
                        'create_date' => $upload->create_date->format('Y-m-d H:i:s'),
                        'user_id' => $upload->user_id,
                        'comment_id' => $upload->comment_id,
                        'upload_name' => $upload->upload_name,
                        'upload_file' => $upload->upload_file,
                        'upload_mime' => $upload->upload_mime,
                        'upload_size' => $upload->upload_size,
                        'thumb_file' => $upload->thumb_file,

                        'repo' => [
                            'id' => $repo->id, 
                            'create_date' => $repo->create_date->format('Y-m-d H:i:s'),
                            'user_id' => $repo->user_id,
                            'repo_name' => $repo->repo_name
                        ],

                        'post' => [
                            'id' => $post->id, 
                            'create_date' => $post->create_date->format('Y-m-d H:i:s'),
                            'user_id' => $post->user_id,
                            'repo_id' => $post->repo_id,
                            'post_status' => $post->post_status,
                            'post_title' => $post->post_title
                        ]
                    ];
                }, $n),
            $uploads)
        ]);

    }

}
