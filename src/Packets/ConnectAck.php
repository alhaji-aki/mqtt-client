<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class ConnectAck extends PacketAbstract implements PacketEvent
{
    use EmptyVariableHeader, DoesntBuildPayload;

    const EVENT = 'CONNACK';

    protected function packetType(): int
    {
        return PacketTypes::CONNACK;
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
        $packetType = ord($input[0]) >> 4;
    }
}
