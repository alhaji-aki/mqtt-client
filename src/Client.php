<?php

namespace AlhajiAki\Mqtt;

use AlhajiAki\Mqtt\Exceptions\ConnectionException;
use AlhajiAki\Mqtt\Exceptions\UnexpectedPacketException;
use AlhajiAki\Mqtt\Packets\Connect;
use AlhajiAki\Mqtt\Packets\ConnectAck;
use AlhajiAki\Mqtt\Packets\Disconnect;
use AlhajiAki\Mqtt\Packets\Factory as PacketsFactory;
use AlhajiAki\Mqtt\Packets\PacketAbstract;
use AlhajiAki\Mqtt\Packets\PingRequest;
use AlhajiAki\Mqtt\Packets\Publish;
use AlhajiAki\Mqtt\Packets\PublishAck;
use AlhajiAki\Mqtt\Packets\PublishReceived;
use AlhajiAki\Mqtt\Packets\PublishRelease;
use AlhajiAki\Mqtt\Packets\Subscribe;
use AlhajiAki\Mqtt\Packets\SubscribeAck;
use AlhajiAki\Mqtt\Packets\Unsubscribe;
use AlhajiAki\Mqtt\Packets\UnsubscribeAck;
use AlhajiAki\Mqtt\Qos\Levels;
use AlhajiAki\Mqtt\Versions\Version311;
use Exception;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

use function React\Promise\resolve;

class Client
{
    /**
     * Host address
     *
     * @var string
     */
    protected $host;

    /**
     * Configurations
     *
     * @var \AlhajiAki\Mqtt\Config
     */
    protected $config;

    /**
     * Version to use
     *
     * @var \AlhajiAki\Mqtt\Contracts\Version
     */
    protected $version;

    /**
     * The connector
     *
     * @var \React\Socket\Connector
     */
    protected $connector;

    /**
     * The react event loop
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(string $host, array $config, LoggerInterface $logger = null)
    {
        $this->host = $host;
        $this->config = new Config(Arr::except($config, 'version'));
        $this->version = isset($config['version']) ? new $config['version'] : new Version311;

        $this->logger = $logger;

        if (!$this->logger) {
            $this->logger = new NullLogger();
        }

        $this->loop = Factory::create();
        $this->connector = new Connector($this->loop);
    }

    public function connect()
    {
        $promise = $this->connector->connect($this->host);

        $promise->then(function (ConnectionInterface $stream) {
            $this->listenersForPackets($stream);

            /**
             * Stop loop, when client disconnected from mqtt server
             */
            $stream->on('end', function () {
                $this->loop()->stop();
            });
        });

        $connection = $promise->then(function (ConnectionInterface $connection) {
            return $this->sendConnectPacket($connection);
        })->then(function (ConnectionInterface $stream) {
            return $this->keepAlive($stream, $this->config->keepAlive);
        })->then(null, function (Exception $exception) {
            $this->logger->debug('Error: ' . $exception->getMessage());
        });

        return $connection;
    }

    public function publish(ConnectionInterface $stream, string $topic, string $message, int $qos = 0, bool $dup = false, bool $retain = false)
    {
        $packet = new Publish($this->version);
        $packet->setTopic($topic);
        $packet->setMessage($message);
        $packet->setQos($qos);
        $packet->setDup($dup);
        $packet->setRetain($retain);

        $success = $this->sendPacketToStream($stream, $packet);

        $deferred = new Deferred();
        if (!$success) {
            $deferred->reject('Publishing failed');
        }

        if ($qos === Levels::AT_LEAST_ONCE_DELIVERY) {
            $stream->on(PublishAck::EVENT, function (PublishAck $message) use ($deferred, $stream) {
                $this->logger->debug('QoS: ' . Levels::AT_LEAST_ONCE_DELIVERY . ', packetId: ' . $message->getPacketId());
                $deferred->resolve($stream);
            });

            return $deferred->promise();
        }

        if ($qos === Levels::EXACTLY_ONCE_DELIVERY) {
            $stream->on(PublishReceived::EVENT, function (PublishReceived $message) use ($stream, $deferred, $packet) {
                if ($packet->getPacketId() === $message->getPacketId()) {
                    $this->logger->debug('QoS: ' . Levels::EXACTLY_ONCE_DELIVERY . ', packetId: ' . $message->getPacketId());

                    $releasePacket = new PublishRelease($this->version);
                    $releasePacket->setPacketId($message->getPacketId());
                    $this->sendPacketToStream($stream, $releasePacket);

                    $deferred->resolve($stream);
                } else {
                    $deferred->reject('Publish Received Ack has wrong packetId');
                }
            });

            return $deferred->promise();
        }

        $deferred->resolve($stream);

        return $deferred->promise();
    }

    public function subscribe(ConnectionInterface $stream, $topics, int $qos = 0)
    {
        $packet = new Subscribe($this->version);

        if (is_array($topics)) {
            $packet->addSubscriptionArray($topics);
        } else {
            $packet->addSubscription($topics, $qos);
        }

        $success = $this->sendPacketToStream($stream, $packet);
        $this->logger->debug('Sent subscription, packetId: ' . $packet->getPacketId());

        $deferred = new Deferred();

        if (!$success) {
            $deferred->reject('Subscribing to topics failed');
        }

        $stream->on(SubscribeAck::EVENT, function (SubscribeAck $acknowledgement) use ($stream, $deferred, $packet) {
            if ($packet->getPacketId() === $acknowledgement->getPacketId()) {
                $this->logger->debug('Subscription successful', $acknowledgement->getResponse($packet->getTopicFilters()));
                $deferred->resolve($stream);
            } else {
                $this->logger->debug('Subscription Acknowledgement has wrong packetId');
                $deferred->reject('Subscription Acknowledgement has wrong packetId');
            }
        });

        return $deferred->promise();
    }

    public function unsubscribe(ConnectionInterface $stream, $topics)
    {
        $packet = new Unsubscribe($this->version);

        if (is_array($topics)) {
            $packet->removeSubscriptionArray($topics);
        } else {
            $packet->removeSubscription($topics);
        }

        $success = $this->sendPacketToStream($stream, $packet);
        $this->logger->debug('Sent unsubscription, packetId: ' . $packet->getPacketId());

        $deferred = new Deferred();

        if (!$success) {
            $deferred->reject('Unsubscribing to topics failed');
        }

        $stream->on(UnsubscribeAck::EVENT, function (UnsubscribeAck $acknowledgement) use ($stream, $deferred, $packet) {
            if ($packet->getPacketId() === $acknowledgement->getPacketId()) {
                $this->logger->debug('Unsubscription successful', $packet->getTopicFilters());
                $deferred->resolve($stream);
            } else {
                $this->logger->debug('Unsubscription Acknowledgement has wrong packetId');
                $deferred->reject('Unsubscription Acknowledgement has wrong packetId');
            }
            $deferred->resolve($stream);
        });

        return $deferred->promise();
    }

    public function disconnect(ConnectionInterface $stream)
    {
        $packet = new Disconnect($this->version);
        $this->sendPacketToStream($stream, $packet);

        return resolve($stream);
    }

    /**
     * Get the loop object
     *
     * @var \React\EventLoop\LoopInterface
     */
    public function loop()
    {
        return $this->loop;
    }

    /**
     * Register listeners for packets
     *
     * @param ConnectionInterface $stream
     * @return void
     */
    protected function listenersForPackets(ConnectionInterface $stream)
    {
        $this->logger->debug('setting listeners');
        $stream->on('data', function ($data) use ($stream) {
            $this->logger->debug('listening for packets');
            try {
                foreach (PacketsFactory::getNextPacket($this->version, $data) as $packet) {
                    $this->logger->debug("Received {$packet->packetTypeString()} from broker");
                    $stream->emit($packet::EVENT, [$packet]);
                }
            } catch (UnexpectedPacketException $exception) {
                $this->logger->debug($exception->getMessage());
                $this->disconnect($stream);
            }
        });
    }

    /**
     * Send the connect packet
     *
     * @param ConnectionInterface $stream
     * @return \React\Promise\PromiseInterface
     */
    protected function sendConnectPacket(ConnectionInterface $stream)
    {
        $packet = new Connect($this->version, $this->config);

        $deferred = new Deferred();
        $stream->on(ConnectAck::EVENT, function (ConnectAck $acknowledgement) use ($stream, $deferred) {
            $this->logger->debug('connection acknowledged');
            if ($acknowledgement->successful()) {
                $deferred->resolve($stream);
            } else {
                $this->logger->debug($acknowledgement->getStatusMessage());
                $deferred->reject(
                    new ConnectionException($acknowledgement->getStatusCode(), $acknowledgement->getStatusMessage())
                );
            }
        });

        $this->sendPacketToStream($stream, $packet);

        return $deferred->promise();
    }

    /**
     * Send packet to stream
     *
     * @param ConnectionInterface $stream
     * @param PacketAbstract $packet
     * @return boolean
     */
    protected function sendPacketToStream(ConnectionInterface $stream, PacketAbstract $packet): bool
    {
        $this->logger->debug("sending {$packet->packetTypeString()} to broker");

        return $stream->write($packet->get());
    }

    protected function keepAlive(ConnectionInterface $stream, int $interval)
    {
        if ($interval > 0) {
            $this->logger->debug('Keep Alive interval is ' . $interval);
            $this->loop()->addPeriodicTimer($interval, function (TimerInterface $timer) use ($stream) {
                $packet = new PingRequest($this->version);
                $this->sendPacketToStream($stream, $packet);
            });
        }

        return resolve($stream);
    }
}
