<?php

use AlhajiAki\Mqtt\Client;

require 'vendor/autoload.php';

$client = new Client('tls://mqtt.zcampusgh.com:8883');

$client->connect();

$client->loop()->run();
