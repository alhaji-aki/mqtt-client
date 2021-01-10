<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\EmptyPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class PingResponse extends PacketAbstract implements PacketEvent
{
    use EmptyVariableHeader, EmptyPayload;

    protected function packetType(): int
    {
        return PacketTypes::PINGRESP;
    }

    public static function parse(Version $version, string $input)
    {
        var_dump($input);
    }
}
