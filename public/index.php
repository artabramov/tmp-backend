<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config.php';

// -- Default timezone --
date_default_timezone_set(APP_TIMEZONE);

// -- Monolog --
Flight::set('monolog', new \Monolog\Logger('app'));
Flight::get('monolog')->pushHandler(new \Monolog\Handler\StreamHandler(MONOLOG_PATH . date('Y-m-d') . '.log'));

// -- Error handle --
Flight::map('error', function(Throwable $e) {

    if($e instanceof \App\Exceptions\AppException) {
        Flight::json([
            'success' => 'false',
            'error' => $e->getMessage()
        ]);

    } else {
        Flight::get('monolog')->debug( $e->getMessage(), [
            'method' => Flight::request()->method,
            'url' => Flight::request()->url,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        Flight::halt(500, 'Internal Server Error!');  
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
$cacheLogger = new \Doctrine\ORM\Cache\Logging\StatisticsCacheLogger();
$cacheConfig->setCacheLogger($cacheLogger);

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

// -- Phpmailer --
$phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true );
$phpmailer->isSMTP(); 
$phpmailer->SMTPDebug = PHPMAILER_DEBUG;
$phpmailer->Host = PHPMAILER_HOST;
$phpmailer->Port = PHPMAILER_PORT;
$phpmailer->SMTPSecure = PHPMAILER_SECURE;
$phpmailer->SMTPAuth = PHPMAILER_AUTH;
$phpmailer->Username = PHPMAILER_USER;
$phpmailer->Password = PHPMAILER_PASS;
$phpmailer->isHTML(true);
$phpmailer->setFrom(PHPMAILER_FROM, PHPMAILER_NAME);
Flight::set('phpmailer', $phpmailer);

// -- Transaction --
Flight::before('start', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->beginTransaction();
});

Flight::after('error', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->rollBack();
});

Flight::after('stop', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->commit();
});

// -- Send json --
Flight::before('json', function( &$params, &$output ) {
    $date = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
    $params[0]['time']['datetime'] = $date->format('Y-m-d H:i:s');
    $params[0]['time']['timezone'] = 'Europe/Moscow';
});

// -- Default --
Flight::route( 'GET /', function() {
});

// -- User register --
Flight::route( 'POST /user', function() {
    $route = new \App\Routes\UserRegister();
    $route->do();
});

// -- User remind --
Flight::route( 'GET /pass', function() {
    $route = new \App\Routes\UserRemind();
    $route->do();
});

// -- User signin --
Flight::route( 'POST /pass', function() {
    $route = new \App\Routes\UserSignin();
    $route->do();
});

// -- User signout --
Flight::route( 'PUT /token', function() {
    $route = new \App\Routes\UserSignout();
    $route->do();
});

// -- User auth --
Flight::route( 'POST /token', function() {
    $route = new \App\Routes\UserAuth();
    $route->do();
});

// -- User select - 
Flight::route( 'GET /user/@user_id', function($user_id) {
    $route = new \App\Routes\UserSelect();
    $route->do($user_id);
});

// -- User update --
Flight::route( 'PUT /user', function() {
    $route = new \App\Routes\UserUpdate();
    $route->do();
});

// -- User query --
Flight::route( 'GET /users', function() {
    $route = new \App\Routes\UserQuery();
    $route->do();
});

// -- Hub insert --
Flight::route( 'POST /hub', function() {
    $route = new \App\Routes\HubInsert();
    $route->do();
});

// -- Hub select - 
Flight::route( 'GET /hub/@hub_id', function($hub_id) {
    $route = new \App\Routes\HubSelect();
    $route->do($hub_id);
});

// -- Hub update --
Flight::route( 'PUT /hub/@hub_id', function($hub_id) {
    $route = new \App\Routes\HubUpdate();
    $route->do($hub_id);
});

// -- Hub delete --
Flight::route( 'DELETE /hub/@hub_id', function($hub_id) {
    $route = new \App\Routes\HubDelete();
    $route->do($hub_id);
});

// -- Hub query --
Flight::route( 'GET /hubs', function() {
    $route = new \App\Routes\HubQuery();
    $route->do();
});

// -- Role insert --
Flight::route( 'POST /role', function() {
    $route = new \App\Routes\RoleInsert();
    $route->do();
});

// -- Role update --
Flight::route( 'PUT /role', function() {
    $route = new \App\Routes\RoleUpdate();
    $route->do();
});

// -- Role delete --
Flight::route( 'DELETE /role', function() {
    $route = new \App\Routes\RoleDelete();
    $route->do();
});

// -- Role query --
Flight::route( 'GET /roles', function() {
    $route = new \App\Routes\RoleQueue();
    $route->do();
});

// -- Go! --
Flight::start();
