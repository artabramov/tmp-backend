<?php

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

$monolog = new Logger( 'project' );
$monolog->pushHandler( new StreamHandler( __DIR__ . '/../../.monolog/' . date('Y-m-d') . '.log' ) );

return $monolog;
