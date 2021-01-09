<?php

namespace AlhajiAki\Mqtt\Versions;

use AlhajiAki\Mqtt\Contracts\Version;

class Version311 implements Version
{
    public function protocolVersion(): int
    {
        return 0x04;
    }
}
