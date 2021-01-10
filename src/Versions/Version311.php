<?php

namespace AlhajiAki\Mqtt\Versions;

use AlhajiAki\Mqtt\Contracts\Version;

class Version311 extends Version
{
    public function protocolVersion()
    {
        return 0x04;
    }
}
