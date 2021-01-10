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
use AlhajiAki\Mqtt\Qos\Levels;
use AlhajiAki\Mqtt\Versions\Version311;
use Exception;
use Illuminate\Support\Arr;
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

    public function __construct(string $host, array $config)
    {
        $this->host = $host;
        $this->config = new Config(Arr::except($config, 'version'));
        $this->version = isset($config['version']) ? new $config['version'] : new Version311;

        $this->loop = Factory::create();
        $this->connector = new Connector($this->loop);
    }

    public function connect()
    {
        $promise = $this->connector->connect($this->host);

        $promise->then(function (ConnectionInterface $stream) {
            $this->listenersForPackets($stream);
        });

        $connection = $promise->then(function (ConnectionInterface $connection) {
            return $this->sendConnectPacket($connection);
        })->then(function (ConnectionInterface $stream) {
            return $this->keepAlive($stream, $this->config->keepAlive);
        })->then(null, function (Exception $exception) {
            var_dump('Error: ' . $exception->getMessage());
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
                var_dump('QoS: ' . Levels::AT_LEAST_ONCE_DELIVERY . ', packetId: ' . $message->getPacketId());
                $deferred->resolve($stream);
            });

            return $deferred->promise();
        }

        if ($qos === Levels::EXACTLY_ONCE_DELIVERY) {
            $stream->on(PublishReceived::EVENT, function (PublishReceived $message) use ($stream, $deferred, $packet) {
                if ($packet->getPacketId() === $message->getPacketId()) {
                    var_dump('QoS: ' . Levels::AT_LEAST_ONCE_DELIVERY . ', packetId: ' . $message->getPacketId());

                    $releasePacket = new PublishRelease($this->version);
                    $releasePacket->setPacketId($message->getPacketId());
                    $stream->write($releasePacket->get());

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

    public function disconnect(ConnectionInterface $stream)
    {
        $packet = new Disconnect($this->version);
        $this->sendPacketToStream($stream, $packet);

        return resolve($stream);
    }

    public function loop()
    {
        return $this->loop;
    }

    protected function listenersForPackets(ConnectionInterface $stream)
    {
        // var_dump('setting listeners');
        $stream->on('data', function ($data) use ($stream) {
            // var_dump('listening for packets');
            try {
                foreach (PacketsFactory::getNextPacket($this->version, $data) as $packet) {
                    $stream->emit($packet::EVENT, [$packet]);
                }
            } catch (UnexpectedPacketException $exception) {
                var_dump($exception->getMessage());
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
        // var_dump('sending connect packet');
        $packet = new Connect($this->version, $this->config);

        $deferred = new Deferred();
        $stream->on(ConnectAck::EVENT, function (ConnectAck $acknowledgement) use ($stream, $deferred) {
            // var_dump('acknowledged');
            if ($acknowledgement->successful()) {
                $deferred->resolve($stream);
            }
            $deferred->reject(
                new ConnectionException($acknowledgement->getStatusCode(), $acknowledgement->getStatusMessage())
            );
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
        // var_dump('sending packet to broker');

        return $stream->write($packet->get());
    }

    protected function keepAlive(ConnectionInterface $stream, int $interval)
    {
        if ($interval > 0) {
            // var_dump('Keep Alive interval is ' . $interval);
            $this->loop()->addPeriodicTimer($interval, function (TimerInterface $timer) use ($stream) {
                $packet = new PingRequest($this->version);
                $this->sendPacketToStream($stream, $packet);
            });
        }

        return resolve($stream);
    }
}
