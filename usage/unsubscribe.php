<?php

use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $topics = [
        'test', 'foo/bar', 'foo/bas',
    ];

    $singleTopic = 'foo/bar';

    $client->unsubscribe($stream, $topics)->then(function (ConnectionInterface $stream) use ($client) {
        /**
         * Disconnect when published
         */
        $client->disconnect($stream);
    });
});


$client->loop()->run();
