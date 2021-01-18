# MQTT Client

This is an MQTT client package to connect to and disconnect from an MQTT broker as well as publish data and subscribe to and unsubscribe to topics.

This mqtt client uses [ReactPHP](https://reactphp.org/) at its core to implement its asynchronous nature and takes inspiration from [alexmorbo/react-mqtt](https://github.com/alexmorbo/react-mqtt) and [oliverlorenz/phpMqttClient](https://github.com/oliverlorenz/phpMqttClient).

## Goal

Goal of this project is easy to use MQTT client for PHP in a modern architecture without using any php modules.

The table shows the versions implemented in this library.
| Version | Specification |
|---|---|
| Version 3.1.1 | http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/csprd02/mqtt-v3.1.1-csprd02.html|

## Installation

You can install the package via composer by running:

```bash
composer require "alhaji-aki/mqtt-client"
```

## Usage

This client can be used in any php application. Check out the 'usage' directory to find example codes.

### initial setup

```php
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
    // 'keep_alive' => 120,
    // 'username' => 'auth_user',
    // 'password' => 'auth_password',
    // 'clean_session' => true, // default is true
    // 'last_will_topic' => '',
    // 'last_will_message' => '',
    // 'last_will_qos' => '',
    // 'last_will_retain' => ''
], $logger);

```

### connect

connecting to a broker/server

```php
<?php

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$client->loop()->run();

```

### disconnect

disconnecting from a broker/server

```php
<?php

use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $client->disconnect($stream);
});

$client->loop()->run();

```

### publishing data

publishing data to the broker

```php
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

```

### subscribing to topics

Subscribing to a single topic

```php
<?php

use AlhajiAki\Mqtt\Packets\Publish;
use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $client->subscribe($stream, 'foo/bar', 0)->then(function (ConnectionInterface $stream) use ($qos) {
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
```

Subscribing to multiple topics

```php
<?php

use AlhajiAki\Mqtt\Packets\Publish;
use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
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
    $client->subscribe($stream, $topics)->then(function (ConnectionInterface $stream) use ($qos) {
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
```

### unsubscribing from topics

Unsubscribing from a single topic

```php
<?php

use AlhajiAki\Mqtt\Packets\Publish;
use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $client->unsubscribe($stream, 'foo/bar', 0)->then(function (ConnectionInterface $stream) use ($qos) {
        /**
         * Disconnect when published
         */
        $client->disconnect($stream);
    });
});

$client->loop()->run();
```

Unsubscribing from multiple topics

```php
<?php

use AlhajiAki\Mqtt\Packets\Publish;
use React\Socket\ConnectionInterface;

require_once __DIR__ . '/index.php';

$connection = $client->connect();

$connection->then(function (ConnectionInterface $stream) use ($client) {
    $topics = ['foo/bar', 'test','foo/bas'];

    $client->unsubscribe($stream, $topics)->then(function (ConnectionInterface $stream) use ($qos) {
        /**
         * Disconnect when published
         */
        $client->disconnect($stream);
    });
});

$client->loop()->run();
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
