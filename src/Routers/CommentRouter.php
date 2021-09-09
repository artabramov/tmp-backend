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
    \App\Wrappers\PostTagWrapper,
    \App\Wrappers\CommentWrapper,
    \App\Wrappers\UploadWrapper;

class CommentRouter
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

    public function insert(string $user_token, int $post_id, string $comment_content) {
 
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

        // comment
        $comment_wrapper = new CommentWrapper($this->em, $this->time);
        $comment = $comment_wrapper->insert($user->id, $post->id, $comment_content);

        // post cache
        $post_term_wrapper = new PostTermWrapper($this->em, $this->time);
        $post_term_wrapper->evict($post->id, 'comments_count');

        return [ 
            'success' => 'true',
            'comment' => [
                'id' => $comment->id
            ]
        ];
    }

    public function delete(string $user_token, int $comment_id) {

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

        // delete comment
        $comment_wrapper->delete($comment, $user_role);

        // post cache
        $post_term_wrapper = new PostTermWrapper($this->em, $this->time);
        $post_term_wrapper->evict($post->id, 'comments_count');


        /*
        // -- Uploads --
        $qb1 = $this->em->createQueryBuilder();
        $qb1->select('upload.id')
            ->from('App\Entities\Upload', 'upload')
            ->where($qb1->expr()->eq('upload.comment_id', $comment->id));

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
        */




        return [ 
            'success' => 'true'
        ];
    }

    public function update(string $user_token, int $comment_id, string $comment_content) {

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

        // update comment
        $comment_wrapper->update($comment, $user_role, $comment_content);

        return [ 
            'success' => 'true'
        ];
    }

    public function list(string $user_token, int $post_id, int $offset) {

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

        // comment
        $comment_wrapper = new CommentWrapper($this->em, $this->time);
        $comments = $comment_wrapper->list($post->id, $offset);

        // delete alerts
        $alert_wrapper = new AlertWrapper($this->em, $this->time);
        $alert_wrapper->delete($user->id, $post->id);

        /*
        // -- Delete alerts --
        $stmt = $this->em->getConnection()->prepare("DELETE FROM alerts WHERE user_id = :user_id AND post_id = :post_id");
        $stmt->bindValue('user_id', $user->id);
        $stmt->bindValue('post_id', $post->id);
        $stmt->execute();
        */

        return [
            'success' => 'true',

            'post' => [
                'id' => $post->id, 
                'create_date' => $post->create_date->format('Y-m-d H:i:s'),
                'user_id' => $post->user_id,
                'repo_id' => $post->repo_id,
                'post_status' => $post->post_status,
                'post_title' => $post->post_title,

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
            ],

            'comments_limit' => $comment_wrapper::LIST_LIMIT,

            'comments_count' => (int) call_user_func( 
                function($post_id) {
                    $term_wrapper = new PostTermWrapper($this->em, $this->time);
                    $term = $term_wrapper->select($post_id, 'comments_count');
                    return empty($term) ? 0 : $term->term_value;
                }, $post->id),

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
                    'upload_file' => $m->upload_file,
                    'upload_size' => $m->upload_size,
                    'thumb_file' => $m->thumb_file
                ], call_user_func( 
                    function($comment_id) {
                        $upload_wrapper = new UploadWrapper($this->em, $this->time);
                        $uploads = $upload_wrapper->list($comment_id);
                        return $uploads;
                    }, $n->id)
                ),

            ], $comments)
        ];
    }

}
