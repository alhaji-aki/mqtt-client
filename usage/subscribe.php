<?php

use AlhajiAki\Mqtt\Packets\Publish;
use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $qos = 0;  // 0
    $topics = [
        [
            'topic' => 'foo/bar',
            'qos' => 1
        ],
        [
            'topic' => 'test',
            'qos' => 0
        ],
        [
            'topic' => 'foo/bas',
            'qos' => 2
        ]
    ];

    $singleTopic = 'foo/bar';

    $client->subscribe($stream, $topics, $qos)->then(function (ConnectionInterface $stream) use ($qos) {
        // Success subscription
        $stream->on(Publish::EVENT, function (Publish $publish) {
            var_dump($publish->getTopic());
            var_dump($publish->getRetain());
            var_dump($publish->getDup());
            var_dump($publish->getQos());
            var_dump($publish->getPayload());
            var_dump($publish->getPacketId());
        });
    });
});


$client->loop()->run();
