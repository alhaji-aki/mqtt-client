<?php

use AlhajiAki\Mqtt\Client;

require 'vendor/autoload.php';

$client = new Client('127.0.0.1:1883', [
    'client_id' => 'newclient',
    'keep_alive' => 120
]);

$client->connect();
// ->then(function (ConnectionInterface $stream) use ($client) {
// $stream->on('end', function () use ($client) {
//     $client->loop()->stop();
// });
// return $client->publish($stream, 'foo/bar', 'example message')
//     ->then(function (ConnectionInterface $stream) use ($client) {
//         /**
//          * Disconnect when published
//          */
//         $client->disconnect($stream);
//     });
// });;

$client->loop()->run();
