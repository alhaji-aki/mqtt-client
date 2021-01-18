<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DefaultFixedHeader;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;

class PublishRelease extends PacketAbstract implements PacketEvent
{
    use DefaultFixedHeader, DoesntBuildPayload;

    const EVENT = 'PUBREL';

    /**
     * @var int
     */
    protected $packetId;

    protected function variableHeader()
    {
        return $this->version->getPacketIdPayload($this->packetId);
    }

    protected function packetType(): int
    {
        return PacketTypes::PUBREL;
    }

    public function packetTypeString(): string
    {
        return 'PUBREL';
    }

    public function setPacketId(int $packetId)
    {
        $this->packetId = $packetId;
    }

    public static function parse(Version $version, $input)
    {
        $packet = new static($version);

        $data = unpack('n', substr($input, 2));
        $packet->setPacketId($data[1]);

        return $packet;
    }
}
