<?php

namespace AlhajiAki\Mqtt\Versions;

use AlhajiAki\Mqtt\Contracts\Version;

class Version311 extends Version
{
    public function protocolVersion()
    {
        return 0x04;
    }

    public function getNextPacketId(): int
    {
        return ($this->packetId = ($this->packetId + 1) & 0xffff);
    }

    public function getPacketIdPayload(int $packetId)
    {
        return chr(($packetId & 0xff00) >> 8) . chr($packetId & 0xff);
    }
}
