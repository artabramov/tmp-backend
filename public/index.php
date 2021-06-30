<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config.php';

// -- Error --
Flight::set('error', '');

// -- Check is flight variable empty --
Flight::map('empty', function($key) {
    return empty( Flight::get($key));
});

// -- Monolog --
Flight::set('monolog', new \Monolog\Logger('app'));
Flight::get('monolog')->pushHandler(new \Monolog\Handler\StreamHandler(MONOLOG_PATH . date('Y-m-d') . '.log'));

// -- Error handle --
Flight::map('error', function(Throwable $e) {
    Flight::log($e);
    Flight::halt(500, 'Internal Server Error!');
});

// -- Log write --
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

// -- Set timezone --
//Flight::get('em')->getConnection()->exec("SET time_zone = '" . APP_TIMEZONE . "'");

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

// -- Send email --
Flight::map( 'email', function( $user_email, $user_name, $email_subject, $email_body ) {
    if( Flight::empty( 'error' )) {
        Flight::get('phpmailer')->addAddress( $user_email, $user_name );
        Flight::get('phpmailer')->Subject = $email_subject;
        Flight::get('phpmailer')->Body = $email_body;
        try {
            Flight::get('phpmailer')->send();
        } catch(\Exception $e) {
            Flight::set('error', 'Server error: email not sent.');
        }
    }
});

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

// -- Update entity
Flight::map('update', function($entity) {
    if(Flight::empty('error')) {
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

// -- Before route --
Flight::before('route', function( &$params, &$output ) {
    Flight::set('time_start', microtime(true));
});

// -- After route --
Flight::before('route', function( &$params, &$output ) {
    Flight::set('time_end', microtime(true));
});

// -- Send json --
Flight::before('json', function( &$params, &$output ) {
    $date = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
    $params[0]['time']['datetime'] = $date->format('Y-m-d H:i:s');
    $params[0]['time']['timezone'] = 'Europe/Moscow';
    $params[0]['time']['timediff'] = Flight::get('time_end') - Flight::get('time_start');
    $params[0]['success'] = Flight::empty('error') ? 'true' : 'false';
    $params[0]['error'] = Flight::empty('error') ? '' : Flight::get('error');
});

// -- Default route --
Flight::route( 'GET /', function() {
});

// -- Register user --
Flight::route( 'POST /user', function() {
    $route = new \App\Routes\UserRegister();
    $route->run();
});

// -- Remind user pass
Flight::route( 'GET /pass', function() {
    $route = new \App\Routes\UserRemind();
    $route->run();
});

// -- Select user - 
Flight::route( 'GET /user/@user_id', function($user_id) {
    $route = new \App\Routes\UserSelect();
    $route->run($user_id);
});

// -- Go! --
Flight::start();
