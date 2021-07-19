<?php
namespace App\Routes;
use \Flight, 
    \DateTime, 
    \DateInterval,
    \Doctrine\DBAL\ParameterType,
    \App\Exceptions\AppException,
    \App\Entities\User, 
    \App\Entities\Usermeta, 
    \App\Entities\Role, 
    \App\Entities\Vol, 
    \App\Entities\Hub, 
    \App\Entities\Hubmeta,
    \App\Entities\Post, 
    \App\Entities\Postmeta,
    \App\Entities\Tag, 
    \App\Entities\Comment,
    \App\Entities\Upload;

class UserAuth
{
    public function do() {

        // -- Vars --

        $user_token = (string) Flight::request()->query['user_token'];

        if(empty($user_token)) {
            throw new AppException('Initial error: user_token is empty.');
        } 

        // -- User --

        $user = Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token]);

        if(empty($user)) {
            throw new AppException('User error: user_token not found.');

        } elseif($user->user_status == 'trash') {
            throw new AppException('User error: user_token is trash.');
        }

        // -- User meta --

        $user_meta = [];
        foreach($user->user_meta as $meta) {
            $user_meta[$meta->meta_key] = $meta->meta_value;
        }

        // -- End --
        
        Flight::json([ 
            'success' => 'true',
            'user' => [
                'id' => $user->id, 
                'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
                'update_date' => $user->update_date->format('Y-m-d H:i:s'), 
                'remind_date' => $user->remind_date->format('Y-m-d H:i:s'), 
                'user_status' => $user->user_status,
                'user_token' => $user->user_token,
                'user_email' => $user->user_email,
                'user_name' => $user->user_name,
                'user_meta' => $user_meta
            ]
        ]);
    }
}
