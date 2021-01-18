<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DefaultFixedHeader;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class ConnectAck extends PacketAbstract implements PacketEvent
{
    use EmptyVariableHeader, DoesntBuildPayload, DefaultFixedHeader;

    const EVENT = 'CONNACK';

    const CONNECTION_SUCCESS = 0;
    const CONNECTION_UNACCEPTABLE_PROTOCOL_VERSION = 1;
    const CONNECTION_IDENTIFIER_REJECTED = 2;
    const CONNECTION_SERVER_UNAVAILABLE = 3;
    const CONNECTION_BAD_CREDENTIALS = 4;
    const CONNECTION_AUTH_ERROR = 5;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $statusMessage;

    protected function packetType(): int
    {
        return PacketTypes::CONNACK;
    }

    public function packetTypeString(): string
    {
        return 'CONNACK';
    }

    /**
     * Parse the data from the server
     *
     * @param Version $version
     * @param string $input
     * @return PacketEvent
     */
    public static function parse(Version $version, string $input)
    {
        $packet = new static($version);

        $statusCode = ord(substr($input, 3));

        $packet->setStatusCode($statusCode);
        $packet->setStatusMessage($statusCode);

        return $packet;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    protected function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    protected function setStatusMessage($statusCode)
    {
        $statuses = [
            self::CONNECTION_SUCCESS => 'Connection Accepted.',
            self::CONNECTION_UNACCEPTABLE_PROTOCOL_VERSION => 'Connection Refused, unacceptable protocol version.',
            self::CONNECTION_IDENTIFIER_REJECTED => 'Connection Refused, identifier rejected.',
            self::CONNECTION_SERVER_UNAVAILABLE => 'Connection Refused, Server unavailable.',
            self::CONNECTION_BAD_CREDENTIALS => 'Connection Refused, bad user name or password.',
            self::CONNECTION_AUTH_ERROR => 'Connection Refused, not authorized.'
        ];

        $this->statusMessage = $statuses[$statusCode];
    }

    public function successful()
    {
        return $this->getStatusCode() === self::CONNECTION_SUCCESS ? true : false;
    }
}
