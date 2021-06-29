<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config.php';

// -- Error --
Flight::set('error', '');

// -- Check is flight-variable empty --
Flight::map('empty', function($key) {
    return empty( Flight::get($key));
});

// -- Monolog --
Flight::set('monolog', new \Monolog\Logger('app'));
Flight::get('monolog')->pushHandler(new \Monolog\Handler\StreamHandler(MONOLOG_PATH . date('Y-m-d') . '.log'));

// -- Errors handle --
Flight::map('error', function(Throwable $e) {
    Flight::log($e);
    Flight::halt(500, 'Internal Server Error!');
});

// -- Logs write --
Flight::map('log', function(Throwable $e) {
    if(APP_DEBUG) {
        Flight::get('monolog')->debug( $e->getMessage(), [
            'method' => Flight::request()->method,
            'url' => Flight::request()->url,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
});

// -- Redis --
$redis = new Redis;
$redis->connect(REDIS_HOST, REDIS_PORT);

// -- Cache --
$cache = new \Doctrine\Common\Cache\RedisCache();
$cache->setRedis($redis);

// -- Doctrine config --
$config = new \Doctrine\ORM\Configuration;

// -- Set up Metadata Drivers --
$driverImpl = $config->newDefaultAnnotationDriver(array(__DIR__ . '/src'));
$config->setMetadataDriverImpl($driverImpl);

// -- Caches --
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

// -- Proxies --
$config->setProxyDir('/tmp');
$config->setProxyNamespace('DoctrineProxies');
$config->setAutoGenerateProxyClasses(true);

// -- Second-level cache --
$cacheConfig = new \Doctrine\ORM\Cache\CacheConfiguration();

// -- Cache logger --
if(APP_DEBUG) {
    $cacheLogger = new \Doctrine\ORM\Cache\Logging\StatisticsCacheLogger();
    $cacheConfig->setCacheLogger($cacheLogger);
}

// -- Cache regions --
$regionConfig = $cacheConfig->getRegionsConfiguration();
$regionConfig->setDefaultLifetime(REDIS_EXPIRED);
$factory = new \Doctrine\ORM\Cache\DefaultCacheFactory($regionConfig, $cache);

// -- Enable second-level cache --
$cacheConfig->setCacheFactory($factory);
$config->setSecondLevelCacheConfiguration($cacheConfig);
$config->setSecondLevelCacheEnabled(true);

// Postgres config
$conn = array(
    'driver' => POSTGRES_DRIVER,
    'host' => POSTGRES_HOST,
    'port' => POSTGRES_PORT,
    'dbname' => POSTGRES_DBNAME,
    'user' => POSTGRES_USER,
    'password' => POSTGRES_PASS
);

// -- Entity manager --
Flight::set('em', \Doctrine\ORM\EntityManager::create($conn, $config));

// -- Save entity --
Flight::map('save', function($entity) {
    if(Flight::empty('error')) {
        Flight::get('em')->persist($entity);
        if(!empty($entity->error)) {
            Flight::set('error', $entity->error);
        }
    }
});

// -- After route --
Flight::after('stop', function( &$params, &$output ) {
    if(Flight::empty('error')) {
        Flight::get('em')->flush();
    }
});

// -- Default route --
Flight::route( 'GET /', function() {
});

// -- Insert user --
Flight::route( 'POST /user', function() {

    // Create user
    $user = new \App\Entities\User();
    $user->user_email = (string) Flight::request()->query['user_email'];
    $user->user_name = (string) Flight::request()->query['user_name'];

    // Save user
    if(Flight::get('em')->getRepository('\App\Entities\User')->findOneBy(['user_email' => $user->user_email])) {
        Flight::set('error', 'User error: email already exists.');
    } else {
        Flight::save($user);
    }

    // Save user addr
    $usermeta = new \App\Entities\Usermeta();
    $usermeta->user_id = $user->id;
    $usermeta->meta_key = 'user_addr';
    $usermeta->meta_value = (string) Flight::request()->query['user_addr'];;
    $usermeta->user = $user;
    Flight::save($usermeta);



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
