<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DefaultFixedHeader;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class UnsubscribeAck extends PacketAbstract implements PacketEvent
{
    use DefaultFixedHeader, DoesntBuildPayload, EmptyVariableHeader;

    const EVENT = 'UNSUBACK';

    protected function packetType(): int
    {
        return PacketTypes::UNSUBACK;
    }

    public function packetTypeString(): string
    {
        return 'UNSUBACK';
    }

    public static function parse(Version $version, $input)
    {
        $packet = new static($version);

        $message = substr($input, 2);
        $data = unpack("n*", $message);

        $packet->setPacketId($data[1]);

        return $packet;
    }

    public function setPacketId(int $packetId)
    {
        $this->packetId = $packetId;
    }

    public function getPacketId(): int
    {
        return $this->packetId;
    }
}
