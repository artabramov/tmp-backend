<?php
namespace App\Wrappers;
use \DateTime,
    \App\Exceptions\AppException,
    \App\Entities\User;

/*
\App\Entities\Alert,
\App\Entities\Comment,
\App\Entities\Hub,
\App\Entities\Hubmeta,
\App\Entities\Post,
\App\Entities\Postmeta,
\App\Entities\Role,
\App\Entities\Tag,
\App\Entities\Upload,
\App\Entities\User,
\App\Entities\Usermeta,
\App\Entities\Vol,
\App\Exceptions\AppException;
*/

class UserWrapper extends Wrapper
{
    public function create(string $user_email, string $user_name, string $user_phone = '') {

        $user_email = mb_strtolower($user_email);
        $user_phone = preg_replace('/[^0-9]/', '', $user_phone) ? !empty($user_phone) : null;

        if($this->em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('user_email is occupied', 2001);

        } elseif(!empty($user_phone) and $em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('user_phone is occupied', 2002);
        }

        // -- User --
        $user = new User();
        $user->create_date = $this->time;
        $user->update_date = new DateTime('1970-01-01 00:00:00');
        $user->remind_date = new DateTime('1970-01-01 00:00:00');
        $user->auth_date = new DateTime('1970-01-01 00:00:00');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_phone = $user_phone;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $this->em->persist($user);
        $this->em->flush();

        // -- End --
        $this->json = [
            'datetime' => [
                'date' => $this->time->format('Y-m-d H:i:s'),
                'timezone' => $this->timezone],
            'success' => 'true',
            'user_id' => $user->id
        ];


        /*
        $em = Flight::get('em');
        $user_email = mb_strtolower((string) Flight::request()->query['user_email']);
        $user_name = (string) Flight::request()->query['user_name'];
        $user_phone = preg_replace('/[^0-9]/', '', (string) Flight::request()->query['user_phone']);
        $user_phone = empty($user_phone) ? null : $user_phone;

        if($em->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('User error: user_email is occupied.');

        } elseif(!empty($user_phone) and $em->getRepository('\App\Entities\User')->findOneBy(['user_phone' => $user_phone])) {
            throw new AppException('User error: user_phone is occupied.');
        }

        // -- User --
        $user = new User();
        $user->create_date = Flight::get('date');
        $user->update_date = Flight::get('zero');
        $user->remind_date = Flight::get('date');
        $user->auth_date = Flight::get('zero');
        $user->user_status = 'pending';
        $user->user_token = $user->create_token();
        $user->user_email = $user_email;
        $user->user_phone = $user_phone;
        $user->user_pass = $user->create_pass();
        $user->user_hash = sha1($user->user_pass);
        $user->user_name = $user_name;
        $em->persist($user);
        $em->flush();

        // -- Hub --
        $hub = new Hub();
        $hub->create_date = Flight::get('date');
        $hub->update_date = Flight::get('zero');
        $hub->user_id = $user->id;
        $hub->hub_name = 'First hub';
        $em->persist($hub);
        $em->flush();

        // -- User role --
        $user_role = new Role();
        $user_role->create_date = Flight::get('date');
        $user_role->update_date = Flight::get('zero');
        $user_role->user_id = $user->id;
        $user_role->hub_id = $hub->id;
        $user_role->role_status = 'admin';
        $user_role->user = $user;
        $user_role->hub = $hub;
        $em->persist($user_role);
        $em->flush();

        // -- User vol --
        $user_vol = new Vol();
        $user_vol->create_date = Flight::get('date');
        $user_vol->update_date = Flight::get('zero');
        $user_vol->expire_date = clone Flight::get('date')->add(new \DateInterval(VOL_DEFAULT_EXPIRE));
        $user_vol->user_id = $user->id;
        $user_vol->vol_size = VOL_DEFAULT_SIZE;
        $em->persist($user_vol);
        $em->flush();

        // -- Post --
        $post = new Post();
        $post->create_date = Flight::get('date');
        $post->update_date = Flight::get('zero');
        $post->user_id = $user->id;
        $post->hub_id = $hub->id;
        $post->post_status = 'doing';
        $post->post_title = 'Hello, world';
        $em->persist($post);
        $em->flush();

        // -- Comment --
        $comment = new Comment();
        $comment->create_date = Flight::get('date');
        $comment->update_date = Flight::get('zero');
        $comment->user_id = $user->id;
        $comment->post_id = $post->id;
        $comment->comment_content = 'First comment';
        $comment->post = $post;
        $em->persist($comment);
        $em->flush();

        // -- Dir --
        if(!file_exists(UPLOAD_PATH . $user->id)) {
            try {
                mkdir(UPLOAD_PATH . $user->id, 0777, true);
            } catch (\Exception $e) {
                throw new AppException('Upload error: make dir error.');
            }
        }

        // -- Email --
        $phpmailer = Flight::get('phpmailer');
        $phpmailer->addAddress($user->user_email, $user->user_name);
        $phpmailer->Subject = 'User register';
        $phpmailer->Body = 'One-time pass: <i>' . $user->user_pass . '</i>';
        $phpmailer->send();

        // -- End --
        Flight::json([
            'success' => 'true',
            'user_id' => $user->id
        ]);
        */
    }
}
