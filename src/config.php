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

// Phpmailer
define('PHPMAILER_DEBUG', 2); // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
define('PHPMAILER_HOST', 'echidna.io');
define('PHPMAILER_PORT', 587);
define('PHPMAILER_SECURE', 'tls');
define('PHPMAILER_AUTH', true);
define('PHPMAILER_USER', 'noreply@echidna.io');
define('PHPMAILER_PASS', 'GxTE4nU8YInsWJRM');
define('PHPMAILER_FROM', 'noreply@echidna.io');
define('PHPMAILER_NAME', 'Echidna');

define('APP_TIMEZONE', 'Europe/Moscow');

define('VOL_DEFAULT_SIZE', '1000000'); // default depot size
define('VOL_DEFAULT_EXPIRE', 'P20Y'); // + 20 years

//
define('APP_REMIND_TIME', 30); // delay between reminds
define('APP_PASS_TIME', 180); // pass expires time
define('APP_QUERY_LIMIT', 3); // number of elements on one page
define('APP_COMMENT_LENGTH', 80);
define('APP_UPLOAD_FILESIZE', '10M'); // max size of one file
define('APP_UPLOAD_NUMBER', 4); // number of maximum size of uploads per one time
define('APP_UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('APP_UPLOAD_MIMES', ['image/png', 'image/gif', 'image/jpeg']); // available mimes: http://www.iana.org/assignments/media-types/media-types.xhtml
