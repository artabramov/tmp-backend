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
        Flight::get('em')->flush();

        if(!empty($entity->error)) {
            Flight::set('error', $entity->error);
        }
    }
});

// -- Open transaction --
Flight::before('start', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->beginTransaction();
});

// -- Close transaction --
Flight::after('stop', function( &$params, &$output ) {
    if(Flight::empty('error')) {
        Flight::get('em')->getConnection()->commit();
    } else {
        Flight::get('em')->getConnection()->rollBack();
    }
});

// -- Send json --
Flight::before('json', function( &$params, &$output ) {
    $date = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
    $params[0]['time']['datetime'] = $date->format('Y-m-d H:i:s');
    $params[0]['time']['timezone'] = 'Europe/Moscow';
    $params[0]['success'] = Flight::empty('error') ? 'true' : 'false';
    $params[0]['error'] = Flight::empty('error') ? '' : Flight::get('error');
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

    // User meta
    $meta = new \App\Entities\Usermeta();
    $meta->user_id = $user->id;
    $meta->meta_key = 'user_addr';
    $meta->meta_value = (string) Flight::request()->query['user_addr'];
    $meta->user = $user;
    Flight::save($meta);

    // Hub
    $hub = new \App\Entities\Hub();
    $hub->user_id = $user->id;
    $hub->hub_name = 'My hub';
    Flight::save($hub);

    // Role
    $role = new \App\Entities\Role();
    $role->user_id = $user->id;
    $role->hub_id = $hub->id;
    $role->role_status = 'admin';
    $role->user = $user;
    $role->hub = $hub;
    Flight::save($role);

    // Stop
    Flight::json([ 
        'user' => Flight::empty('error') ? [
            'id' => $user->id, 
            'create_date' => $user->create_date->format('Y-m-d H:i:s'), 
            'user_status' => $user->user_status,
            'user_name' => $user->user_name ] 
        : [],
    ]);

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

    $user_roles = $user->user_roles;
    foreach($user_roles as $user_role) {
        echo $user_role->hub_id . ': ' . $user_role->role_status . PHP_EOL;
    }

    echo PHP_EOL;
    echo microtime(true) - $starttime;

});

// -- Go! --
Flight::start();
