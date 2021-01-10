<?php

namespace AlhajiAki\Mqtt\Contracts;

abstract class Version
{
    abstract public function protocolVersion();

    public function protocolIdentifierString()
    {
        return 'MQTT';
    }
}
