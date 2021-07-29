<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config.php';

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

// --
//Flight::set('cache', $cache);

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

    $stmt = Flight::get('em')->getConnection()->prepare("SELECT to_timestamp(0)");
    $stmt->execute();
    Flight::set('zero', new DateTime($stmt->fetchOne()));

    $stmt = Flight::get('em')->getConnection()->prepare("SELECT NOW()::timestamp(0)");
    $stmt->execute();
    Flight::set('date', new DateTime($stmt->fetchOne()));

    $stmt = Flight::get('em')->getConnection()->prepare("SELECT current_setting('TIMEZONE')");
    $stmt->execute();
    Flight::set('timezone', $stmt->fetchOne());
});

Flight::after('error', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->rollBack();
});

Flight::after('stop', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->commit();
});

// -- Send json --
Flight::before('json', function( &$params, &$output ) {
    $params[0]['datetime']['date'] = Flight::get('date')->format('Y-m-d H:i:s');
    $params[0]['datetime']['timezone'] = Flight::get('timezone');
});

// -- Default --
Flight::route('GET /', function() {
    require_once(__DIR__ . '/webapp/index.php');
});

// -- User register --
Flight::route('POST /api/user', function() {
    $route = new \App\Routes\UserRegister();
    $route->do();
});

// -- User remind --
Flight::route('GET /api/pass', function() {
    $route = new \App\Routes\UserRemind();
    $route->do();
});

// -- User signin --
Flight::route('POST /api/pass', function() {
    $route = new \App\Routes\UserSignin();
    $route->do();
});

// -- User signout --
Flight::route('PUT /api/token', function() {
    $route = new \App\Routes\UserSignout();
    $route->do();
});

// -- User select - 
Flight::route('GET /api/user/@user_id', function($user_id) {
    $route = new \App\Routes\UserSelect();
    $route->do($user_id);
});

// -- User update --
Flight::route('PUT /api/user', function() {
    $route = new \App\Routes\UserUpdate();
    $route->do();
});

// -- User query --
Flight::route('GET /api/users', function() {
    $route = new \App\Routes\UserQuery();
    $route->do();
});

// -- Hub insert --
Flight::route('POST /api/hub', function() {
    $route = new \App\Routes\HubInsert();
    $route->do();
});

// -- Hub select - 
Flight::route('GET /api/hub/@hub_id', function($hub_id) {
    $route = new \App\Routes\HubSelect();
    $route->do($hub_id);
});

// -- Hub update --
Flight::route('PUT /api/hub/@hub_id', function($hub_id) {
    $route = new \App\Routes\HubUpdate();
    $route->do($hub_id);
});

// -- Hub delete --
Flight::route('DELETE /api/hub/@hub_id', function($hub_id) {
    $route = new \App\Routes\HubDelete();
    $route->do($hub_id);
});

// -- Hub query --
Flight::route('GET /api/hubs', function() {
    $route = new \App\Routes\HubQuery();
    $route->do();
});

// -- Role insert --
Flight::route('POST /api/role', function() {
    $route = new \App\Routes\RoleInsert();
    $route->do();
});

// -- Role update --
Flight::route('PUT /api/role', function() {
    $route = new \App\Routes\RoleUpdate();
    $route->do();
});

// -- Role delete --
Flight::route('DELETE /api/role', function() {
    $route = new \App\Routes\RoleDelete();
    $route->do();
});

// -- Role query --
Flight::route('GET /api/roles', function() {
    $route = new \App\Routes\RoleQuery();
    $route->do();
});

// -- Post insert --
Flight::route('POST /api/post', function() {
    $route = new \App\Routes\PostInsert();
    $route->do();
});

// -- Post select --
Flight::route('GET /api/post/@post_id', function($post_id) {
    $route = new \App\Routes\PostSelect();
    $route->do($post_id);
});

// -- Post update --
Flight::route('PUT /api/post/@post_id', function($post_id) {
    $route = new \App\Routes\PostUpdate();
    $route->do($post_id);
});

// -- Post delete --
Flight::route('DELETE /api/post/@post_id', function($post_id) {
    $route = new \App\Routes\PostDelete();
    $route->do($post_id);
});

// -- Post query --
Flight::route('GET /api/posts', function() {
    $route = new \App\Routes\PostQuery();
    $route->do();
});

// -- Comment insert --
Flight::route('POST /api/comment', function() {
    $route = new \App\Routes\CommentInsert();
    $route->do();
});

// -- Comment update --
Flight::route('PUT /api/comment/@comment_id', function($comment_id) {
    $route = new \App\Routes\CommentUpdate();
    $route->do($comment_id);
});

// -- Comment delete --
Flight::route('DELETE /api/comment/@comment_id', function($comment_id) {
    $route = new \App\Routes\CommentDelete();
    $route->do($comment_id);
});

// -- Comment custom --
Flight::route('GET /api/comments', function() {
    $route = new \App\Routes\CommentQuery();
    $route->do();
});

// -- Upload insert --
Flight::route('POST /api/upload', function() {
    $route = new \App\Routes\UploadInsert();
    $route->do();
});

// -- Upload delete --
Flight::route('DELETE /api/upload/@upload_id', function($upload_id) {
    $route = new \App\Routes\UploadDelete();
    $route->do($upload_id);
});

// -- Vol select --
Flight::route('GET /api/vol', function() {
    $route = new \App\Routes\VolSelect();
    $route->do();
});

// -- Go! --
Flight::start();
