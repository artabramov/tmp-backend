<?php
namespace App\Routes;

class HubSequence
{
    public function run() {

        // -- Auth --
        $auth = \Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_token' => (string) \Flight::request()->query['user_token']]);

        if(empty($auth)) {
            \Flight::set('error', 'Hub sequence error: user_token not found.');

        } elseif($auth->user_status == 'trash') {
            \Flight::set('error', 'Hub sequence error: user_status is trash.');
        }


        


        // -- End --
        \Flight::json(['hubs'=>'hubs']);
    }
}
