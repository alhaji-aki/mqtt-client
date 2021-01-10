<?php

namespace AlhajiAki\Mqtt\Traits;

trait DefaultFixedHeader
{
    protected function fixedHeader(): string
    {
        return chr($this->packetType()) . $this->remainingLength();
    }
}
