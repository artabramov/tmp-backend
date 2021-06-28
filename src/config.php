<?php

// Redis
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_EXPIRED', 7200); // expired in seconds

// Postgres
define('POSTGRES_DRIVER', 'pdo_pgsql');
define('POSTGRES_HOST', 'localhost');
define('POSTGRES_PORT', 5432);
define('POSTGRES_DBNAME', 'postgres');
define('POSTGRES_USER', 'postgres');
define('POSTGRES_PASS', '123456');

// Monolog
define('MONOLOG_PATH', __DIR__ . '/../.monolog/');

// App
define('APP_DEBUG', true);
