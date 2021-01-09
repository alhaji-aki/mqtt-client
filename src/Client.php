<?php

namespace AlhajiAki\Mqtt;

use AlhajiAki\Mqtt\Packets\Connect;
use AlhajiAki\Mqtt\Versions\Version311;
use Exception;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class Client
{
    /**
     * Host address
     *
     * @var string
     */
    protected $host;

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

    public function __construct(string $host)
    {
        $this->host = $host;

        $this->loop = Factory::create();
        $this->connector = new Connector($this->loop);
    }

    public function connect()
    {
        $this->connector->connect($this->host)->then(function (ConnectionInterface $connection) {
            var_dump('connected');


            // send connect packet
            $packet = new Connect(new Version311, new Options);

            var_dump($packet->get());
            // $connection->write($packet->build());
            // $connection->pipe(new WritableResourceStream(STDOUT, $loop));
            $this->loop()->stop();
        })->then(null, function (Exception $exception) {
            var_dump($exception->getMessage());
        });
    }

    public function loop()
    {
        return $this->loop;
    }
}
