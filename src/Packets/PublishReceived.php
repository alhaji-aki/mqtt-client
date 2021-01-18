<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DefaultFixedHeader;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class PublishReceived extends PacketAbstract implements PacketEvent
{
    use DefaultFixedHeader, DoesntBuildPayload, EmptyVariableHeader;

    const EVENT = 'PUBREC';

    protected function packetType(): int
    {
        return PacketTypes::PUBREC;
    }

    public function packetTypeString(): string
    {
        return 'PUBREC';
    }

    public static function parse(Version $version, $input)
    {
        $packet = new static($version);

        $data = unpack('n', substr($input, 2));
        $packet->setPacketId($data[1]);

        return $packet;
    }

    /**
     * @param $messageId
     */
    public function setPacketId($messageId)
    {
        $this->packetId = $messageId;
    }

    /**
     * @return int
     */
    public function getPacketId(): int
    {
        return $this->packetId;
    }
}
