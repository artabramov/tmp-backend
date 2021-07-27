<?php
namespace App\Routes;
use \Flight,
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

class UserSelect
{
    public function do($user_id) {
    
        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];
        $user_id = (int) $user_id;

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        if($user->id == $user_id) {

            // -- Alerts sum --
            $tmp = $user->user_meta->filter(function($element) {
                return $element->meta_key == 'alerts_sum';
            })->first();
            $alerts_sum = !empty($tmp->meta_value) ? $tmp->meta_value : 0;

            // -- Uploads sum --
            $tmp = $user->user_meta->filter(function($element) {
                return $element->meta_key == 'uploads_sum';
            })->first();
            $uploads_sum = !empty($tmp->meta_value) ? $tmp->meta_value : 0;

            // -- Roles count --
            $tmp = $user->user_meta->filter(function($element) {
                return $element->meta_key == 'roles_count';
            })->first();
            $roles_count = !empty($tmp->meta_value) ? $tmp->meta_value : 0;

            // -- User vol (view) --
            $stmt = $em->getConnection()->prepare("SELECT vol_id FROM vw_users_vols WHERE user_id = :user_id LIMIT 1");
            $stmt->bindValue('user_id', $user->id);
            $stmt->execute();
            $user_vol = $em->find('App\Entities\Vol', $stmt->fetchOne());

            // -- End --
            Flight::json([
                'success' => 'true',
                'user' => [
                    'id' => $user->id, 
                    'create_date' => $user->create_date->format('Y-m-d H:i:s'),
                    'user_status' => $user->user_status,
                    'user_token' => $user->user_token,
                    'user_email' => $user->user_email,
                    'user_phone' => !empty($user->user_phone) ? $user->user_phone : '',
                    'user_name' => $user->user_name,

                    'user_meta' => [
                        'roles_count' => $roles_count,
                        'uploads_sum' => $uploads_sum,
                        'alerts_sum' => $alerts_sum
                    ],

                    'user_vol' => [
                        'create_date' => $user_vol->create_date->format('Y-m-d H:i:s'),
                        'expire_date' => $user_vol->expire_date->format('Y-m-d H:i:s'),
                        'vol_size' => $user_vol->vol_size
                    ]
                ]
            ]);

        } else {

            // -- Member ---
            $member = $em->find('\App\Entities\User', $user_id);

            if(empty($member)) {
                throw new AppException('Member error: user_id not found.');
            }

            // -- End --
            Flight::json([
                'success' => 'true',
                'user' => [
                    'id' => $member->id, 
                    'create_date' => $member->create_date->format('Y-m-d H:i:s'),
                    'user_status' => $member->user_status,
                    'user_name' => $member->user_name
                ]
            ]);
        }

    }
}
