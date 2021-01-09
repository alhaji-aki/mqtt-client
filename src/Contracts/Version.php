<?php

namespace AlhajiAki\Mqtt\Contracts;

interface Version{
    public function protocolVersion() : int;
}
