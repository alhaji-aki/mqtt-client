<?php

use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $data = [
        'foo' => 'bar',
        'bar' => 'baz',
        'time' => time(),
    ];

    $qos = 2;  // 0

    $client->publish($stream, 'foo/bar', json_encode($data), $qos, false, false)
        ->then(function (ConnectionInterface $stream) use ($client) {
            /**
             * Disconnect when published
             */
            $client->disconnect($stream);
        });
});


$client->loop()->run();
