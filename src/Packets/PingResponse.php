<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class PingResponse extends PacketAbstract implements PacketEvent
{
    use EmptyVariableHeader, DoesntBuildPayload;

    const EVENT = 'PING_RESPONSE';

    protected function packetType(): int
    {
        return PacketTypes::PINGRESP;
    }

    public static function parse(Version $version, string $input)
    {
        $packet = new static($version);

        return $packet;
    }
}
