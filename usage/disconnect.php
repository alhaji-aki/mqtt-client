<?php

use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $client->disconnect($stream);
});

$client->loop()->run();
