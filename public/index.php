<?php
header('Access-Control-Allow-Origin: *');

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
            'error' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]
        ]);

    } else {
        Flight::get('monolog')->debug( $e->getMessage(), [
            'method' => Flight::request()->method,
            'url' => Flight::request()->url,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        Flight::halt(500, 'Internal Server Error');  
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
    Flight::set('microtime', microtime(true));

    $stmt = Flight::get('em')->getConnection()->prepare("SET TIME ZONE 'Etc/UTC'");
    $stmt->execute();
});

Flight::after('error', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->rollBack();
});

Flight::after('stop', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->commit();
});

// -- Send json --
Flight::before('json', function( &$params, &$output ) {
    $params[0]['datetime']['date'] = Flight::datetime()->format('Y-m-d H:i:s');
    $params[0]['datetime']['timezone'] = Flight::datetime()->getTimezone()->getName();
    $params[0]['debug']['microtime'] = microtime(true) - Flight::get('microtime');
});

// datetime
Flight::map('datetime', function() {

    if(!Flight::has('timestamp_em')) {
        $stmt = Flight::get('em')->getConnection()->prepare("SELECT NOW()::timestamp(0)");
        $stmt->execute();
        $datetime = new DateTime($stmt->fetchOne());
        Flight::set('timestamp_em', $datetime->getTimestamp());

        $stmt = Flight::get('em')->getConnection()->prepare("SELECT current_setting('TIMEZONE')");
        $stmt->execute();
        Flight::set('timezone_em', $stmt->fetchOne());

        $datetime = new DateTime('now');
        Flight::set('timestamp_php', $datetime->getTimestamp());
    }

    $datetime = new DateTime('now');
    $timestamp = $datetime->getTimestamp();
    $time = $timestamp - Flight::get('timestamp_php');

    $timestamp_em = Flight::get('timestamp_em') + $time;
    $datetime_em = new DateTime();
    $datetime_em->setTimestamp($timestamp_em);
    $datetime_em->setTimezone(new DateTimeZone(Flight::get('timezone_em')));

    return $datetime_em;
});

// -- Default --
Flight::route('GET /', function() {
    require_once(__DIR__ . '/webapp/index.php');
});

// -- User insert --
Flight::route('POST /user', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_name']
    );
});

// -- User select - 
Flight::route('GET /user/@user_id', function($user_id) {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (int) $user_id
    );
});

// -- User update --
Flight::route('PUT /user', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_name']
    );
});

// -- User list --
Flight::route('GET /users', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['offset'],
    );
});

// -- User remind --
Flight::route('GET /pass', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->remind(
        (string) Flight::request()->query['user_email']
    );
});

// -- User signin --
Flight::route('POST /pass', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->signin(
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_pass']
    );
});

// -- User signout --
Flight::route('PUT /token', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->signout(
        (string) Flight::request()->query['user_token']
    );
});

// -- User auth --
Flight::route('POST /token', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->auth(
        (string) Flight::request()->query['user_token']
    );
});

// -- User auto find --
Flight::route('GET /users/find/@like_text', function($like_text) {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->find(
        (string) Flight::request()->query['user_token'],
        (string) $like_text
    );
});

// -- Thumb --
Flight::route('POST /thumb', function() {
    $files = Flight::request()->files->getData();
    $file = array_shift($files);

    $wrapper = new \App\Wrappers\ThumbWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        !empty($file) ? $file : []
    );
});

// -- User timezone --
Flight::route('PUT /timezone', function() {
    $wrapper = new \App\Wrappers\TimezoneWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_timezone']
    );
});

// -- User bio --
Flight::route('PUT /bio', function() {
    $wrapper = new \App\Wrappers\BioWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_bio']
    );
});

// -- Repo insert --
Flight::route('POST /repo', function() {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['repo_name'],
    );
});

// -- Repo select - 
Flight::route('GET /repo/@repo_id', function($repo_id) {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
    );
});

// -- Repo update --
Flight::route('PUT /repo/@repo_id', function($repo_id) {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
        (string) Flight::request()->query['repo_name'],
    );
});

// -- Repo delete --
Flight::route('DELETE /repo/@repo_id', function($repo_id) {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
    );
});

// -- Repos list --
Flight::route('GET /repos', function() {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Role insert --
Flight::route('POST /role', function() {
    $wrapper = new \App\Wrappers\RoleWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_email'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['role_status'],
    );
});

// -- Role update --
Flight::route('PUT /role', function() {
    $wrapper = new \App\Wrappers\RoleWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['user_id'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['role_status'],
    );
});

// -- Role delete --
Flight::route('DELETE /role', function() {
    $wrapper = new \App\Wrappers\RoleWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['user_id'],
        (int) Flight::request()->query['repo_id'],
    );
});

// -- Role query --
Flight::route('GET /roles', function() {
    $wrapper = new \App\Wrappers\RoleWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Post insert --
Flight::route('POST /post', function() {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['post_status'],
        (string) Flight::request()->query['post_title'],
        (string) Flight::request()->query['post_tags'],
    );
});

// -- Post select --
Flight::route('GET /post/@post_id', function($post_id) {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
    );
});

// -- Post update --
Flight::route('PUT /post/@post_id', function($post_id) {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
        (string) Flight::request()->query['post_status'],
        (string) Flight::request()->query['post_title'],
        (string) Flight::request()->query['post_tags'],
    );
});

// -- Post delete --
Flight::route('DELETE /post/@post_id', function($post_id) {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
    );
});

// -- Posts list --
Flight::route('GET /posts', function() {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['post_status'],
        (string) Flight::request()->query['post_title'],
        (string) Flight::request()->query['post_tag'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Comment insert --
Flight::route('POST /comment', function() {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['post_id'],
        (string) Flight::request()->query['comment_content'],
    );
});

// -- Comment update --
Flight::route('PUT /comment/@comment_id', function($comment_id) {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $comment_id,
        (string) Flight::request()->query['comment_content'],
    );
});

// -- Comment delete --
Flight::route('DELETE /comment/@comment_id', function($comment_id) {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $comment_id,
    );
});

// -- Comment list --
Flight::route('GET /comments', function() {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['post_id'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Upload insert --
Flight::route('POST /upload', function() {
    $wrapper = new \App\Wrappers\UploadWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['comment_id'],
        Flight::request()->files->getData()
    );
});

// -- Upload update --
Flight::route('PUT /upload/@upload_id', function($upload_id) {
    $wrapper = new \App\Wrappers\UploadWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $upload_id,
        (string) Flight::request()->query['upload_name'],
    );
});

// -- Upload delete --
Flight::route('DELETE /upload/@upload_id', function($upload_id) {
    $wrapper = new \App\Wrappers\UploadWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $upload_id
    );
});

// -- Premium select --
Flight::route('GET /premium', function() {
    $wrapper = new \App\Wrappers\PremiumWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['premium_code'],
    );
});

// -- POST cache --
Flight::route('POST /cache', function() {
    $wrapper = new \App\Wrappers\CacheWrapper(Flight::get('em'));
    $wrapper->create();
});

// -- GET cache --
Flight::route('GET /cache', function() {
    $wrapper = new \App\Wrappers\CacheWrapper(Flight::get('em'));
    $wrapper->read();
});

// -- Go! --
Flight::start();
