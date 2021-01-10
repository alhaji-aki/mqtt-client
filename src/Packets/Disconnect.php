<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class Disconnect extends PacketAbstract
{
    use EmptyVariableHeader, DoesntBuildPayload;

    protected function packetType(): int
    {
        return PacketTypes::DISCONNECT;
    }
}