<?php
namespace App\Routes;
use \Flight, \DateTime;
use \App\Entities\User, \App\Entities\Usermeta, \App\Entities\Hub, \App\Entities\Hubmeta, \App\Entities\Role;
use \App\Exceptions\AppException;

class UserRegister
{
    public function do() {

        // -- Initial --
        $user_email = (string) Flight::request()->query['user_email'];
        $user_name = (string) Flight::request()->query['user_name'];

        if(empty($user_email)) {
            throw new AppException('Initial error: user_email is empty.');

        } elseif(empty($user_name)) {
            throw new AppException('Initial error: user_name is empty.');
        }  

        // -- Auth --
        if(Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user_email])) {
            throw new AppException('Auth error: user_email is occupied.');
        }

        $auth = new User();
        $auth->remind_date = new DateTime('now');
        $auth->user_status = 'pending';
        $auth->user_token = $auth->create_token();
        $auth->user_pass = $auth->create_pass();
        $auth->user_hash = sha1($auth->user_pass);
        $auth->user_email = $user_email;
        $auth->user_name = $user_name;
        Flight::get('em')->persist($auth);
        Flight::get('em')->flush();
        
        /*
        // -- Auth meta --
        $auth_meta = new Usermeta();
        $auth_meta->user_id = $auth->id;
        $auth_meta->meta_key = 'user_ip';
        $auth_meta->meta_value = Flight::request()->ip;
        $auth_meta->user = $auth;
        Flight::get('em')->persist($auth_meta);
        Flight::get('em')->flush();
        */

        // -- Hub --
        $hub = new Hub();
        $hub->hub_status = 'custom';
        $hub->user_id = $auth->id;
        $hub->hub_name = 'My hub';
        Flight::get('em')->persist($hub);
        Flight::get('em')->flush();

        // -- Auth role --
        $auth_role = new Role();
        $auth_role->user_id = $auth->id;
        $auth_role->hub_id = $hub->id;
        $auth_role->role_status = 'admin';
        $auth_role->user = $auth;
        $auth_role->hub = $hub;
        Flight::get('em')->persist($auth_role);
        Flight::get('em')->flush();

        // -- Count roles (user meta) --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.user_id', $auth->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $auth_meta = new Usermeta();
        $auth_meta->user_id = $auth->id;
        $auth_meta->meta_key = 'roles_count';
        $auth_meta->meta_value = $qb1_result[0][1];
        $auth_meta->user = $auth;
        Flight::get('em')->persist($auth_meta);
        Flight::get('em')->flush();

        // -- Count roles (hub meta) --
        $qb1 = Flight::get('em')->createQueryBuilder();
        $qb1->select('count(role.id)')->from('App\Entities\Role', 'role')->where($qb1->expr()->eq('role.hub_id', $hub->id));
        $qb1_result = $qb1->getQuery()->getResult();

        $hub_meta = new Hubmeta();
        $hub_meta->hub_id = $hub->id;
        $hub_meta->meta_key = 'roles_count';
        $hub_meta->meta_value = $qb1_result[0][1];
        $hub_meta->hub = $hub;
        Flight::get('em')->persist($hub_meta);
        Flight::get('em')->flush();

        // -- Email --
        Flight::get('phpmailer')->addAddress($auth->user_email, $auth->user_name);
        Flight::get('phpmailer')->Subject = 'User register';
        Flight::get('phpmailer')->Body = 'One-time pass: <i>' . $auth->user_pass . '</i>';
        Flight::get('phpmailer')->send();

        // -- End --
        Flight::json([ 
            'success' => 'true',
            'user' =>  [
                'id' => $auth->id, 
                'create_date' => $auth->create_date->format('Y-m-d H:i:s'), 
                'user_status' => $auth->user_status,
                'user_name' => $auth->user_name
            ]
        ]);
    }
}
