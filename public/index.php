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
$em = \Doctrine\ORM\EntityManager::create($conn, $config);
Flight::set('em', $em);

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

// -- Time --
$time = new App\Services\Time(Flight::get('em'));
Flight::set('time', $time);

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
    $params[0]['time']['datetime'] = Flight::get('time')->datetime->format('Y-m-d H:i:s');
    $params[0]['time']['timezone'] = Flight::get('time')->datetime->getTimezone()->getName();
    $params[0]['time']['timecost'] = microtime(true) - Flight::get('microtime');
});

/*
// -- Find one term_value by parent entity and term_key --
Flight::map('get_user_term_value', function($user_id, $term_key) {
});
*/

/*
// -- Get all terms of parent entity --
Flight::map('get_terms', function($entity) {
    
    $reflect = new ReflectionClass($entity);
    $class = $reflect->getShortName();

    $qb1 = Flight::get('em')->createQueryBuilder();
    $qb1->select('term.id')->from('App\Entities\\' . $class . 'Term', 'term')
        ->where($qb1->expr()->eq('term.' . strtolower($class) . '_id', $entity->id));
    $qb1_res = $qb1->getQuery()->getResult();

    $terms = [];
    foreach($qb1_res as $res) {
        array_push($terms, Flight::get('em')->find('App\Entities\\' . $class . 'Term', $res['id']));
    }
    return $terms;
});
*/

/*
// -- Count alerts of parent entity and user_id --
Flight::map('count_alerts', function($entity, $user) {

    $reflect = new ReflectionClass($entity);
    $class = $reflect->getShortName();

    $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
    $rsm->addScalarResult('alerts_count', 'alerts_count');

    if($class == 'User') {
        $q1 = Flight::get('em')
            ->createNativeQuery("SELECT alerts_count FROM vw_users_alerts WHERE user_id = :user_id LIMIT 1", $rsm)
            ->setParameter('user_id', $user->id);

    } elseif($class == 'Repo') {
        $q1 = Flight::get('em')
            ->createNativeQuery("SELECT alerts_count FROM vw_repos_alerts WHERE user_id = :user_id AND repo_id = :repo_id  LIMIT 1", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('repo_id', $entity->id);

    } elseif($class == 'Post') {
        $q1 = Flight::get('em')
            ->createNativeQuery("SELECT alerts_count FROM vw_posts_alerts WHERE user_id = :user_id AND post_id = :post_id  LIMIT 1", $rsm)
            ->setParameter('user_id', $user->id)
            ->setParameter('post_id', $entity->id);
    }

    $q1_res = $q1->getResult();
    return !empty($q1_res[0]) ? $q1_res[0]['alerts_count'] : 0;
});
*/

/*
// -- Evict terms from cache --
Flight::map('evict_terms', function($entity) {

    $reflect = new ReflectionClass($entity);
    $class = $reflect->getShortName();

    $qb1 = Flight::get('em')->createQueryBuilder();
    $qb1->select('term.id')->from('App\Entities\\' . $class . 'Term', 'term')
        ->where($qb1->expr()->eq('term.' . strtolower($class) . '_id', $entity->id));
    $qb1_res = $qb1->getQuery()->getResult();

    foreach($qb1_res as $res) {
        if(Flight::get('em')->getCache()->containsEntity('App\Entities\\' . $class . 'Term', $term->id)) {
            Flight::get('em')->getCache()->evictEntity('App\Entities\\' . $class . 'Term', $term->id);
        }
    }
});
*/


/*
// -- Temp --
Flight::route('GET /temp', function() {

    $qb1 = $this->em->createQueryBuilder();

    $qb2->select('role.repo_id')
        ->from('App\Entities\UserRole', 'role')
        ->where($qb2->expr()->eq('role.user_id', $user->id));
    $terms = array_map(fn($n) => $this->em->find('App\Entities\Repo', $n['id']), $qb1->getQuery()->getResult());


    Flight::json([
        'success' => 'true',
    ]);
});
*/

/*
// -- Default --
Flight::route('GET /', function() {
    //require_once(__DIR__ . '/webapp/index.php');
    //phpinfo();
});
*/

// -- User register --
Flight::route('POST /user', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->register(
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_name'],
        (string) Flight::request()->query['user_timezone']
    ));
});

// -- User remind --
Flight::route('GET /pass', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->remind(
        (string) Flight::request()->query['user_email']
    ));
});

// -- User signin --
Flight::route('POST /pass', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->signin(
        (string) Flight::request()->query['user_email'],
        (string) Flight::request()->query['user_pass']
    ));
});

// -- User signout --
Flight::route('PUT /token', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->signout(
        (string) Flight::request()->query['user_token']
    ));
});

// -- User update --
Flight::route('PUT /user', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->update(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_name'],
        (string) Flight::request()->query['user_timezone']
    ));
});

// -- User auth --
Flight::route('POST /token', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->auth(
        (string) Flight::request()->query['user_token']
    ));
});

// -- User select - 
Flight::route('GET /user/@user_id', function($user_id) {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->select(
        (string) Flight::request()->query['user_token'],
        (int) $user_id
    ));
});

// -- User list --
Flight::route('GET /users', function() {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['offset'],
    ));
});

// -- User auto find --
Flight::route('GET /users/find/@value', function($value) {
    $router = new \App\Routers\UserRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->find(
        (string) Flight::request()->query['user_token'],
        (string) $value
    ));
});

// -- Repo insert --
Flight::route('POST /repo', function() {
    $router = new \App\Routers\RepoRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->insert(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['repo_name'],
    ));
});

// -- Repo select - 
Flight::route('GET /repo/@repo_id', function($repo_id) {
    $router = new \App\Routers\RepoRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->select(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
    ));
});

// -- Repo update --
Flight::route('PUT /repo/@repo_id', function($repo_id) {
    $router = new \App\Routers\RepoRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->update(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
        (string) Flight::request()->query['repo_name'],
    ));
});

// -- Repo delete --
Flight::route('DELETE /repo/@repo_id', function($repo_id) {
    $router = new \App\Routers\RepoRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->delete(
        (string) Flight::request()->query['user_token'],
        (int) $repo_id,
    ));
});

// -- Repos list --
Flight::route('GET /repos', function() {
    $router = new \App\Routers\RepoRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['offset'],
    ));
});

// -- Role insert --
Flight::route('POST /role', function() {
    $router = new \App\Routers\RoleRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->insert(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_email'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['role_status'],
    ));
});

// -- Role update --
Flight::route('PUT /role', function() {
    $router = new \App\Routers\RoleRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->update(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['user_id'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['role_status'],
    ));
});

// -- Role delete --
Flight::route('DELETE /role', function() {
    $router = new \App\Routers\RoleRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->delete(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['user_id'],
        (int) Flight::request()->query['repo_id'],
    ));
});

// -- Role query --
Flight::route('GET /roles', function() {
    $router = new \App\Routers\RoleRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (int) Flight::request()->query['offset'],
    ));
});

// -- Post insert --
Flight::route('POST /post', function() {
    $router = new \App\Routers\PostRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['post_status'],
        (string) Flight::request()->query['post_title'],
        (string) Flight::request()->query['post_tags'],
    ));
});

// -- Post update --
Flight::route('PUT /post/@post_id', function($post_id) {
    $router = new \App\Routers\PostRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->update(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
        (string) Flight::request()->query['post_status'],
        (string) Flight::request()->query['post_title'],
        (string) Flight::request()->query['post_tags'],
    ));
});

// -- Post select --
Flight::route('GET /post/@post_id', function($post_id) {
    $router = new \App\Routers\PostRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->select(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
    ));
});

// -- Post delete --
Flight::route('DELETE /post/@post_id', function($post_id) {
    $router = new \App\Routers\PostRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->delete(
        (string) Flight::request()->query['user_token'],
        (int) $post_id,
    ));
});

// -- Posts list --
Flight::route('GET /posts', function() {
    $router = new \App\Routers\PostRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['repo_id'],
        (string) Flight::request()->query['post_status'],
        (int) Flight::request()->query['offset'],
    ));
});

// -- Comment insert --
Flight::route('POST /comment', function() {
    $router = new \App\Routers\CommentRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['post_id'],
        (string) Flight::request()->query['comment_content'],
    ));
});

// -- Comment delete --
Flight::route('DELETE /comment/@comment_id', function($comment_id) {
    $router = new \App\Routers\CommentRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->delete(
        (string) Flight::request()->query['user_token'],
        (int) $comment_id,
    ));
});

// -- Comment update --
Flight::route('PUT /comment/@comment_id', function($comment_id) {
    $router = new \App\Routers\CommentRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->update(
        (string) Flight::request()->query['user_token'],
        (int) $comment_id,
        (string) Flight::request()->query['comment_content'],
    ));
});

// -- Comment list --
Flight::route('GET /comments', function() {
    $router = new \App\Routers\CommentRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->list(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['post_id'],
        (int) Flight::request()->query['offset'],
    ));
});

// -- Upload insert --
Flight::route('POST /upload', function() {
    $router = new \App\Routers\UploadRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->insert(
        (string) Flight::request()->query['user_token'],
        (int) Flight::request()->query['comment_id'],
        Flight::request()->files->getData()
    ));
});

// -- Upload update --
Flight::route('PUT /upload/@upload_id', function($upload_id) {
    $router = new \App\Routers\UploadRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->update(
        (string) Flight::request()->query['user_token'],
        (int) $upload_id,
        (string) Flight::request()->query['upload_name'],
    ));
});

// -- Upload delete --
Flight::route('DELETE /upload/@upload_id', function($upload_id) {
    $router = new \App\Routers\UploadRouter(Flight::get('em'), Flight::get('time'));
    Flight::json($router->delete(
        (string) Flight::request()->query['user_token'],
        (int) $upload_id
    ));
});








// -- Thumb --
Flight::route('POST /thumb', function() {
    $files = Flight::request()->files->getData();
    $file = array_shift($files);

    $router = new \App\Routers\ThumbRouter(Flight::get('em'));
    $router->insert(
        (string) Flight::request()->query['user_token'],
        !empty($file) ? $file : []
    );
});

// -- User bio --
Flight::route('PUT /bio', function() {
    $router = new \App\Routers\BioRouter(Flight::get('em'));
    $router->update(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['user_bio']
    );
});

// -- Posts by tag --
Flight::route('GET /bytag', function() {
    $router = new \App\Routers\PostRouter(Flight::get('em'));
    $router->bytag(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['post_tag'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Posts by title --
Flight::route('GET /bytitle', function() {
    $router = new \App\Routers\PostRouter(Flight::get('em'));
    $router->bytitle(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['post_title'],
        (int) Flight::request()->query['offset'],
    );
});

// -- Premium select --
Flight::route('GET /premium', function() {
    $router = new \App\Routers\PremiumRouter(Flight::get('em'));
    $router->select(
        (string) Flight::request()->query['user_token'],
        (string) Flight::request()->query['premium_code'],
    );
});

// -- POST cache --
Flight::route('POST /cache', function() {
    $router = new \App\Routers\CacheRouter(Flight::get('em'));
    $router->create();
});

// -- GET cache --
Flight::route('GET /cache', function() {
    $router = new \App\Routers\CacheRouter(Flight::get('em'));
    $router->read();
});

// -- Go! --
Flight::start();
