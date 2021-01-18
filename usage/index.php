<?php

use AlhajiAki\Mqtt\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('logs', [
    new StreamHandler(__DIR__ . '/../logs/app.log')
]);

$client = new Client('127.0.0.1:1883', [
    'client_id' => 'newclient',
    'keep_alive' => 120
], $logger);
