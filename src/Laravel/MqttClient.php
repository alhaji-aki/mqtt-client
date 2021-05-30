<?php

namespace AlhajiAki\Mqtt\Laravel;

use Closure;
use Illuminate\Support\Facades\Log;
use Morbo\React\Mqtt\Client;
use Morbo\React\Mqtt\ConnectionOptions;
use Morbo\React\Mqtt\Packets\Publish;
use Morbo\React\Mqtt\Packets\UnsubscribeAck;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;

class MqttClient
{
    /**
     * @var string
     */
    protected $host = null;

    /**
     * @var \Morbo\React\Mqtt\ConnectionOptions
     */
    protected $options = null;

    /**
     * @var \Morbo\React\Mqtt\Client
     */
    protected $client;

    /**
     * @var \React\Promise\PromiseInterface
     */
    protected $connection;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    public function __construct(array $config)
    {
        $this->host = $this->setAddress($config['host'], $config['protocol']);

        $this->version = new $config['version'];

        $this->options = new ConnectionOptions([
            'username' => $config['username'],
            'password' => $config['password'],
            'clientId' => $config['client_id'] . '-' . uniqid(),
            'cleanSession' => $config['clean_session'],
            'willTopic' => $config['will_topic'],
            'willMessage' => $config['will_message'],
            'willQos' => $config['will_qos'],
            'willRetain' => $config['will_retain'],
            'keepAlive' => $config['keep_alive'],
        ]);

        $this->loop = Factory::create();

        $debugger = $config['debug'] ?  Log::getLogger() : null;
        $this->client = new Client($this->loop, new $this->version, $debugger);
    }

    /**
     * Makes a connection to the broker
     *
     * @return \AlhajiAki\MqttClient\MqttClient
     */
    public function connect()
    {
        $this->connection = $this->client->connect($this->host, $this->options);

        return $this;
    }

    /**
     * Publish data to a topic
     *
     * @param string $topic
     * @param string $data
     * @param integer $qos
     * @param boolean $dup
     * @param boolean $retain
     * @return void
     */
    public function publish(string $topic, string $data, int $qos = 0, bool $dup = false, bool $retain = false)
    {
        $args = func_get_args();

        $this->connection->then(function (ConnectionInterface $stream) use ($args) {
            $this->listenForEnd($stream);

            $this->client->publish($stream, ...$args)->then(function (ConnectionInterface $publishStream) {
                /**
                 * Disconnect when published
                 */
                $this->disconnect();
            });
        });

        $this->run();
    }

    /**
     * Subscribe to a topic
     *
     * @param string $topic
     * @param Closure $successListener
     * @param integer $qos
     * @param Closure $errorListener
     * @return void
     */
    public function subscribe(string $topic, Closure $successListener, int $qos = 0, Closure $errorListener = null)
    {
        $this->connection->then(function (ConnectionInterface $stream) use ($topic, $successListener, $qos, $errorListener) {
            $this->client->subscribe($stream, $topic, $qos)
                ->then(function (ConnectionInterface $stream) use ($successListener) {
                    $stream->on(Publish::EVENT, $successListener);
                }, $errorListener);
        });

        $this->run();
    }

    /**
     * Unsubscribe from a topic
     *
     * @param string $topic
     * @param Closure $successListener
     * @param Closure $errorListener
     * @return void
     */
    public function unsubscribe(string $topic, Closure $successListener = null, Closure $errorListener = null)
    {
        $this->connection->then(function (ConnectionInterface $stream) use ($topic, $successListener) {
            $this->listenForEnd($stream);

            $this->client->unsubscribe($stream, $topic)
                ->then(function (ConnectionInterface $stream) use ($successListener) {
                    $stream->on(UnsubscribeAck::EVENT, $successListener);

                    /**
                     * Disconnect when unsubscribed
                     */
                    $this->disconnect();
                });
        })->then(null, $errorListener);

        $this->run();
    }

    /**
     * Disconnect from the broker
     *
     * @return void
     */
    public function disconnect()
    {
        $this->connection->then(function (ConnectionInterface $stream) {
            $this->client->disconnect($stream);
        });
    }

    /**
     * Returns an instance of the connection
     *
     * @return \React\Promise\PromiseInterface
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Run the loop to perform action
     *
     * @return void
     */
    protected function run()
    {
        $this->loop->run();
    }

    /**
     * Stop loop, when client disconnected from mqtt server
     */
    protected function listenForEnd(ConnectionInterface $stream)
    {
        $stream->on('end', function () {
            $this->loop->stop();
        });
    }

    /**
     * Set the right address to use
     *
     * @param string $host
     * @param string $protocol
     * @return string
     */
    private function setAddress(string $host, string $protocol)
    {
        return $protocol . '://' . $host;
    }
}
