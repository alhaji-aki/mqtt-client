<?php

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$client->loop()->run();
