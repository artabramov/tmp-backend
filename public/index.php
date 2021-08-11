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
    Flight::set('microtime', microtime(true));
});

Flight::after('error', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->rollBack();
});

Flight::after('stop', function( &$params, &$output ) {
    Flight::get('em')->getConnection()->commit();
});

// -- Send json --
Flight::before('json', function( &$params, &$output ) {
    //header('Content-Type: application/json');
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
Flight::route('POST /api/user', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_name'],
        (string) Flight::request()->query['user_phone']
    );
});

// -- User select - 
Flight::route('GET /api/user/@user_id', function($user_id) {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (int) $user_id
    );
});

// -- User update --
Flight::route('PUT /api/user', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_phone'],
        (string) Flight::request()->query['user_name'],
    );
});

// -- User list --
Flight::route('GET /api/users', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['offset'],
    );
});

// -- User remind --
Flight::route('GET /api/pass', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->remind(
        (string) Flight::request()->query['user_email']
    );
});

// -- User signin --
Flight::route('POST /api/pass', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->signin(
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_pass']
    );
});

// -- User signout --
Flight::route('PUT /api/token', function() {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->signout(
        (string) Flight::request()->query['user_token']
    );
});

// -- User auto find --
Flight::route('GET /api/user/search/@search_text', function($search_text) {
    $wrapper = new \App\Wrappers\UserWrapper(Flight::get('em'));
    $wrapper->find(
        (string) Flight::request()->query['user_token'],
        (string) $search_text
    );
});

// -- Repo insert --
Flight::route('POST /api/repo', function() {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['repo_name'],
    );
});

// -- Repo select - 
Flight::route('GET /api/repo/@repo_id', function($repo_id) {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
    );
});

// -- Repo update --
Flight::route('PUT /api/repo/@repo_id', function($repo_id) {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
        (string) Flight::request()->query['repo_name'],
    );
});

// -- Repo delete --
Flight::route('DELETE /api/repo/@repo_id', function($repo_id) {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
    );
});

// -- Repos list --
Flight::route('GET /api/repos', function() {
    $wrapper = new \App\Wrappers\RepoWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Role insert --
Flight::route('POST /api/role', function() {
    $wrapper = new \App\Wrappers\UserRoleWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_email'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['role_status'],
    );
});

// -- Role update --
Flight::route('PUT /api/role', function() {
    $wrapper = new \App\Wrappers\UserRoleWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['user_id'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['role_status'],
    );
});

// -- Role delete --
Flight::route('DELETE /api/role', function() {
    $wrapper = new \App\Wrappers\UserRoleWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['user_id'],
        (int) Flight::request()->query['repo_id'],
    );
});

// -- Role query --
Flight::route('GET /api/roles', function() {
    $wrapper = new \App\Wrappers\UserRoleWrapper(Flight::get('em'));
    $wrapper->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Post insert --
Flight::route('POST /api/post', function() {
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
Flight::route('GET /api/post/@post_id', function($post_id) {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
    );
});

// -- Post update --
Flight::route('PUT /api/post/@post_id', function($post_id) {
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
Flight::route('DELETE /api/post/@post_id', function($post_id) {
    $wrapper = new \App\Wrappers\PostWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
    );
});

// -- Comment insert --
Flight::route('POST /api/comment', function() {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['post_id'],
        (string) Flight::request()->query['comment_content'],
    );
});

// -- Comment update --
Flight::route('PUT /api/comment/@comment_id', function($comment_id) {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $comment_id,
        (string) Flight::request()->query['comment_content'],
    );
});

// -- Comment delete --
Flight::route('DELETE /api/comment/@comment_id', function($comment_id) {
    $wrapper = new \App\Wrappers\CommentWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $comment_id,
    );
});

// -- Upload insert --
Flight::route('POST /api/upload', function() {
    $wrapper = new \App\Wrappers\UploadWrapper(Flight::get('em'));
    $wrapper->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['comment_id'],
        Flight::request()->files->getData()
    );
});

// -- Upload update --
Flight::route('PUT /api/upload/@upload_id', function($upload_id) {
    $wrapper = new \App\Wrappers\UploadWrapper(Flight::get('em'));
    $wrapper->update(
        (string) Flight::request()->query['user_token'],
        (int) $upload_id,
        (string) Flight::request()->query['upload_name'],
    );
});

// -- Upload delete --
Flight::route('DELETE /api/upload/@upload_id', function($upload_id) {
    $wrapper = new \App\Wrappers\UploadWrapper(Flight::get('em'));
    $wrapper->delete(
        (string) Flight::request()->query['user_token'],
        (int) $upload_id
    );
});

// -- Premium select --
Flight::route('GET /api/premium', function() {
    $wrapper = new \App\Wrappers\PremiumWrapper(Flight::get('em'));
    $wrapper->select(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['premium_key'],
    );
});

// -- POST cache --
Flight::route('POST /api/cache', function() {
    $wrapper = new \App\Wrappers\CacheWrapper(Flight::get('em'));
    $wrapper->create();
});

// -- GET cache --
Flight::route('GET /api/cache', function() {
    $wrapper = new \App\Wrappers\CacheWrapper(Flight::get('em'));
    $wrapper->read();
});







// -- Post query --
Flight::route('GET /api/posts', function() {
    $route = new \App\Routes\PostQuery();
    $route->do();
});

// -- Comment custom --
Flight::route('GET /api/comments', function() {
    $route = new \App\Routes\CommentQuery();
    $route->do();
});

// -- Go! --
Flight::start();
