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

class VolSelect
{
    public function do() {
    
        $em = Flight::get('em');
        $user_token = (string) Flight::request()->query['user_token'];

        // -- User --
        $user = $em->getRepository('\App\Entities\User')->findOneBy(['user_token' => $user_token, 'user_status' => 'approved']);

        if(empty($user)) {
            throw new AppException('User error: user not found or not approved.');
        }

        // -- User vol (view) --
        $stmt = $em->getConnection()->prepare("SELECT vol_id FROM vw_users_vols WHERE user_id = :user_id LIMIT 1");
        $stmt->bindValue('user_id', $user->id);
        $stmt->execute();
        $user_vol = $em->find('App\Entities\Vol', $stmt->fetchOne());

        // -- End --
        Flight::json([
            'success' => 'true',
            'user_vol' => [
                'create_date' => $user_vol->create_date->format('Y-m-d H:i:s'),
                'expire_date' => $user_vol->expire_date->format('Y-m-d H:i:s'),
                'vol_size' => $user_vol->vol_size
            ]
        ]);



    }
}
