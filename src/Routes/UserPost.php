<?php
namespace App\Routes;

class UserPost
{
    protected $error;
    protected $em;

    public function __construct() {

        $user_name = (string) Flight::request()->query['user_name'];

        // Insert user
        $user = new \App\Entities\User();
        $user->user_status = 'pending';
        $user->user_token = sha1(rand(1,10000)) . sha1(rand(1,10000));
        $user->user_email = sha1(rand(1,10000)) . '@noreply.no';
        $user->user_hash = sha1(rand(1,10000));
        $user->user_name = $user_name;
        Flight::save($user);

    /*
    // insert user meta 1
    $usermeta = new \App\Entities\Usermeta();
    $usermeta->user_id = $user;
    $usermeta->meta_key = 'key1';
    $usermeta->meta_value = 'value1';
    $usermeta->user = $user;
    Flight::get('em')->persist($usermeta);

    // insert user meta 2
    $usermeta = new \App\Entities\Usermeta();
    $usermeta->user_id = $user;
    $usermeta->meta_key = 'key2';
    $usermeta->meta_value = 'value2';
    $usermeta->user = $user;
    Flight::get('em')->persist($usermeta);

    if(empty($user->error)) {
        Flight::get('em')->flush();

        echo PHP_EOL;
        echo $user->id;
    } else {

        echo PHP_EOL;
        echo $user->error;
    }
    */

    echo(Flight::get('error'));


});

// -- Select user - 
Flight::route( 'GET /user/@user_id', function( $user_id ) {

    $starttime = microtime(true);
    
    // select user
    $user = Flight::get('em')->find('\App\Entities\User', $user_id);
    echo $user->id . ': ' . $user->user_name . PHP_EOL;

    $user_meta = $user->user_meta;
    foreach($user_meta as $meta) {
        echo $meta->id . ': ' . $meta->meta_key . ': ' . $meta->meta_value . PHP_EOL;
    }

    echo PHP_EOL;
    echo microtime(true) - $starttime;

});

// -- Go! --
Flight::start();
