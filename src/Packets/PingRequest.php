<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Traits\EmptyPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class PingRequest extends PacketAbstract
{
    use EmptyVariableHeader, EmptyPayload;

    protected function packetType(): int
    {
        return PacketTypes::PINGREQ;
    }
}
