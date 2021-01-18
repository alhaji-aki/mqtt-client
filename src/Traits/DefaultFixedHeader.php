<?php

namespace AlhajiAki\Mqtt\Traits;

trait DefaultFixedHeader
{
    protected function fixedHeader(): string
    {
        return chr($this->packetType() << 4) . $this->encodeRemainingLength($this->remainingLength());
    }
}
