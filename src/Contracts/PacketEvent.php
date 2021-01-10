<?php

namespace AlhajiAki\Mqtt\Contracts;

interface PacketEvent
{
    public static function parse(Version $version, string $input);
}
