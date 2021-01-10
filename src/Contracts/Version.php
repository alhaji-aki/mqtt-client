<?php

namespace AlhajiAki\Mqtt\Contracts;

abstract class Version
{
    /**
     * @var int
     */
    protected $packetId;

    public function __construct()
    {
        // Reduce risk of creating duplicate ids in sequential sessions
        $this->packetId = rand(1, 100) * 100;
    }

    abstract public function protocolVersion();

    abstract public function getNextPacketId(): int;

    /**
     * @param int|null $packetId
     * @return string
     */
    abstract public function getPacketIdPayload(int $packetId);

    public function protocolIdentifierString()
    {
        return 'MQTT';
    }
}
