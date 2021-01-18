<?php

use AlhajiAki\Mqtt\Qos\Levels;
use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $data = [
        'foo' => 'bar',
        'bar' => 'baz',
        'time' => time(),
    ];

    $qos = Levels::AT_MOST_ONCE_DELIVERY;  // 0

    $client->publish($stream, 'foo/bar', json_encode($data), $qos)
        ->then(function (ConnectionInterface $stream) use ($client) {
            /**
             * Disconnect when published
             */
            $client->disconnect($stream);
        });
});


$client->loop()->run();
